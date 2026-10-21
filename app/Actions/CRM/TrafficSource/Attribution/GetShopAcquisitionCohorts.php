<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource\Attribution;

use App\Actions\CRM\TrafficSource\GetAttributionStartedAt;
use App\Actions\CRM\TrafficSource\ParseTrafficSourceTouches;
use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetShopAcquisitionCohorts
{
    use AsAction;

    public const array WINDOWS = [30, 90, 180, 365];

    public const string NO_FIRST_TOUCH = 'no_first_touch';

    private const int COHORT_MONTHS = 6;

    private const int DAY = 86400;

    /**
     * @return array<string, mixed>
     */
    public function handle(Shop $shop, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $startedAt = GetAttributionStartedAt::run();

        if ($startedAt && (!$from || $from->lt($startedAt))) {
            $from = $startedAt;
        }

        $key = implode(':', ['marketing-v2-acquisition', $shop->id, $from?->timestamp ?? 0, $to?->timestamp ?? 0]);

        return Cache::remember($key, now()->addMinutes(10), fn () => $this->calculate($shop, $from, $to, $startedAt));
    }

    private function calculate(Shop $shop, ?Carbon $from, ?Carbon $to, ?Carbon $startedAt): array
    {
        $now         = now()->getTimestamp();
        $cohortStart = now()->startOfMonth()->subMonths(self::COHORT_MONTHS - 1);

        if ($startedAt && $cohortStart->lt($startedAt)) {
            $cohortStart = $startedAt->copy()->startOfMonth();
        }

        $loadFrom  = $from && $from->lt($cohortStart) ? $from : $cohortStart;
        $customers = $this->customers($shop, $loadFrom);

        $periodCustomers = $customers->filter(
            fn (array $customer) => (!$from || $customer['registered_at'] >= $from->getTimestamp())
                && (!$to || $customer['registered_at'] <= $to->getTimestamp())
        );

        $spend = GetShopOrderAttribution::spendByType($shop, $from, $to);
        $names = DB::table('traffic_sources')->where('shop_id', $shop->id)->pluck('name', 'type')->all();

        $channels = $periodCustomers
            ->groupBy('channel')
            ->map(fn (Collection $group, string $channel) => $this->channelFigures($channel, $group, $spend[$channel] ?? 0, $names, $now))
            ->sortBy(fn (array $row) => [$row['type'] === self::NO_FIRST_TOUCH ? 1 : 0, -$row['buyers'], -$row['registrations'], $row['name']])
            ->values();

        $paid         = $channels->where('is_paid', true);
        $paidSpend    = array_sum(array_intersect_key($spend, $paid->keyBy('type')->all()));
        $paidBuyers   = $paid->sum('buyers');
        $registrations = $periodCustomers->count();
        $buyers       = $channels->sum('buyers');
        $noFirstTouch = $channels->firstWhere('type', self::NO_FIRST_TOUCH);

        $paidChannelsWithoutCustomers = collect($spend)
            ->filter(fn (float $amount, string $type) => $amount > 0 && !$channels->contains('type', $type))
            ->map(fn (float $amount, string $type) => $this->channelFigures($type, collect(), $amount, $names, $now))
            ->values();

        return [
            'from'          => $from?->toDateString(),
            'to'            => $to?->toDateString(),
            'currency_code' => $shop->currency->code,
            'windows'       => self::WINDOWS,
            'totals'        => [
                'registrations'       => $registrations,
                'buyers'              => $buyers,
                'paid_registrations'  => $paid->sum('registrations'),
                'paid_buyers'         => $paidBuyers,
                'paid_spend'          => round($paidSpend, 2),
                'paid_cac'            => $paidBuyers > 0 && $paidSpend > 0 ? round($paidSpend / $paidBuyers, 2) : null,
                'no_first_touch'      => $noFirstTouch['registrations'] ?? 0,
                'no_first_touch_buyers' => $noFirstTouch['buyers'] ?? 0,
            ],
            'channels'      => $channels->concat($paidChannelsWithoutCustomers)->all(),
            'cohorts'       => $this->cohorts($customers->filter(fn (array $customer) => $customer['registered_at'] >= $cohortStart->getTimestamp()), $cohortStart, $names, $now),
        ];
    }

    /**
     * @return Collection<int, array{id: int, registered_at: int, channel: string, orders: array<int, array{placed_at: int, net_amount: float}>}>
     */
    private function customers(Shop $shop, Carbon $from): Collection
    {
        $splitter = SplitOrderAttribution::make();

        $customers = DB::table('customers')
            ->where('shop_id', $shop->id)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $from)
            ->select('id', 'traffic_sources', 'created_at')
            ->get()
            ->map(function (object $customer) use ($splitter) {
                $registeredAt     = Carbon::parse($customer->created_at)->getTimestamp();
                $acquisitionTouch = $splitter->acquisitionTouch(
                    $splitter->usableTouches(ParseTrafficSourceTouches::run($customer->traffic_sources)),
                    $registeredAt
                );

                return [
                    'id'            => $customer->id,
                    'registered_at' => $registeredAt,
                    'channel'       => $acquisitionTouch ? $acquisitionTouch['type']->value : self::NO_FIRST_TOUCH,
                    'orders'        => [],
                ];
            })
            ->keyBy('id');

        DB::table('orders')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('customers.shop_id', $shop->id)
            ->whereNull('customers.deleted_at')
            ->where('customers.created_at', '>=', $from)
            ->whereNotIn('orders.state', [OrderStateEnum::CREATING->value, OrderStateEnum::CANCELLED->value])
            ->whereNull('orders.deleted_at')
            ->select('orders.customer_id', 'orders.net_amount')
            ->selectRaw(GetShopOrderAttribution::ORDER_PLACED_AT.' as placed_at')
            ->get()
            ->each(function (object $order) use ($customers) {
                $customer = $customers->get($order->customer_id);

                if (!$customer) {
                    return;
                }

                $customer['orders'][] = [
                    'placed_at'  => Carbon::parse($order->placed_at)->getTimestamp(),
                    'net_amount' => (float) $order->net_amount,
                ];
                $customers->put($order->customer_id, $customer);
            });

        return $customers->values();
    }

    /**
     * @param Collection<int, array{id: int, registered_at: int, channel: string, orders: array<int, array{placed_at: int, net_amount: float}>}> $customers
     * @param array<string, string> $names
     *
     * @return array<string, mixed>
     */
    private function channelFigures(string $channel, Collection $customers, float $spend, array $names, int $now): array
    {
        $buyers = $customers->filter(fn (array $customer) => !empty($customer['orders']))->count();

        return [
            'type'          => $channel,
            'name'          => $this->channelName($channel, $names),
            'is_paid'       => (bool) TrafficSourcesTypeEnum::tryFrom($channel)?->isPaid(),
            'registrations' => $customers->count(),
            'buyers'        => $buyers,
            'spend'         => round($spend, 2),
            'cac'           => $buyers > 0 && $spend > 0 ? round($spend / $buyers, 2) : null,
            'windows'       => $this->windowFigures($customers, $now),
        ];
    }

    /**
     * @param Collection<int, array{registered_at: int, orders: array<int, array{placed_at: int, net_amount: float}>}> $customers
     *
     * @return array<int, array{days: int, revenue: float, buyers: int, revenue_per_buyer: float|null, state: string}>
     */
    private function windowFigures(Collection $customers, int $now): array
    {
        $earliest = $customers->min('registered_at');
        $latest   = $customers->max('registered_at');

        $previousWindows = array_combine(self::WINDOWS, array_merge([0], array_slice(self::WINDOWS, 0, -1)));

        return array_map(function (int $days) use ($customers, $earliest, $latest, $now, $previousWindows) {
            $revenue = 0.0;
            $buyers  = 0;

            foreach ($customers as $customer) {
                $windowEnd = $customer['registered_at'] + $days * self::DAY;
                $inWindow  = array_filter($customer['orders'], fn (array $order) => $order['placed_at'] <= $windowEnd);

                if ($inWindow) {
                    $buyers++;
                    $revenue += array_sum(array_column($inWindow, 'net_amount'));
                }
            }

            $state = match (true) {
                $earliest === null                                         => 'not_yet',
                $latest + $days * self::DAY <= $now                        => 'final',
                $earliest + $previousWindows[$days] * self::DAY <= $now    => 'growing',
                default                                                    => 'not_yet',
            };

            return [
                'days'              => $days,
                'revenue'           => round($revenue, 2),
                'buyers'            => $buyers,
                'revenue_per_buyer' => $buyers > 0 && $state !== 'not_yet' ? round($revenue / $buyers, 2) : null,
                'state'             => $state,
            ];
        }, self::WINDOWS);
    }

    /**
     * @param Collection<int, array{registered_at: int, channel: string, orders: array<int, array{placed_at: int, net_amount: float}>}> $customers
     * @param array<string, string> $names
     *
     * @return array{months: array<int, array{key: string, label: string}>, channels: array<int, array{type: string, name: string, rows: array<int, array<string, mixed>>}>}
     */
    private function cohorts(Collection $customers, Carbon $cohortStart, array $names, int $now): array
    {
        $months = [];

        for ($month = $cohortStart->copy(); $month->lte(now()); $month->addMonth()) {
            $months[$month->format('Y-m')] = $month->translatedFormat('M Y');
        }

        $channels = $customers
            ->groupBy('channel')
            ->map(function (Collection $group, string $channel) use ($months, $names, $now) {
                $byMonth = $group->groupBy(fn (array $customer) => Carbon::createFromTimestamp($customer['registered_at'])->format('Y-m'));

                return [
                    'type'      => $channel,
                    'name'      => $this->channelName($channel, $names),
                    'customers' => $group->count(),
                    'rows'      => collect($months)->map(fn (string $label, string $key) => [
                        'key'       => $key,
                        'label'     => $label,
                        'customers' => $byMonth->get($key, collect())->count(),
                        'cells'     => $this->windowFigures($byMonth->get($key, collect()), $now),
                    ])->reverse()->values()->all(),
                ];
            })
            ->sortByDesc('customers')
            ->values()
            ->all();

        return [
            'months'   => collect($months)->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])->values()->all(),
            'channels' => $channels,
        ];
    }

    /**
     * @param array<string, string> $names
     */
    private function channelName(string $channel, array $names): string
    {
        if ($channel === self::NO_FIRST_TOUCH) {
            return __('No first touch');
        }

        return $names[$channel] ?? TrafficSourcesTypeEnum::labels()[$channel] ?? $channel;
    }
}
