<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource\Attribution;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomerValueCurve;
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

    /** @var array<int, array<int, float|null>>|null value curve ratios by point and base day */
    private ?array $ratios = null;

    /**
     * @return array<string, mixed>
     */
    public function handle(Shop $shop, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $startedAt = GetAttributionStartedAt::run();

        if ($startedAt && (!$from || $from->lt($startedAt))) {
            $from = $startedAt;
        }

        $curve = $shop->crmStats?->customer_value_curve;
        $key   = implode(':', ['marketing-v2-acquisition', $shop->id, $from?->timestamp ?? 0, $to?->timestamp ?? 0, $shop->crmStats?->customer_value_curve_hydrated_at?->timestamp ?? 0]);

        return Cache::remember($key, now()->addMinutes(10), fn () => $this->calculate($shop, $from, $to, $startedAt, $curve));
    }

    private function calculate(Shop $shop, ?Carbon $from, ?Carbon $to, ?Carbon $startedAt, ?array $curve): array
    {
        $this->ratios = $curve ? collect($curve['points'])->mapWithKeys(fn (array $point) => [$point['days'] => $point['ratios']])->all() : null;

        $now         = now()->getTimestamp();
        $cohortStart = now()->startOfMonth()->subMonths(self::COHORT_MONTHS - 1);

        if ($startedAt && $cohortStart->lt($startedAt)) {
            $cohortStart = $startedAt->copy()->startOfMonth();
        }

        $loadFrom  = $from && $from->lt($cohortStart) ? $from : $cohortStart;
        $customers = $this->customers($shop, $loadFrom, $curve['average_margin'] ?? null);

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
        $paidTypes    = $paid->pluck('type')->all();
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
                'paid_value'          => $this->valueFigures(
                    $periodCustomers->filter(fn (array $customer) => in_array($customer['channel'], $paidTypes, true)),
                    $paidBuyers > 0 && $paidSpend > 0 ? $paidSpend / $paidBuyers : null,
                    $now
                ),
            ],
            'value'         => $curve ? [
                'horizons'       => array_keys(ShopHydrateCustomerValueCurve::HORIZONS),
                'hydrated_at'    => $shop->crmStats->customer_value_curve_hydrated_at?->toDateString(),
                'average_margin' => $curve['average_margin'],
                'cost_coverage'  => $curve['cost_coverage'],
                'cohorts'        => collect($curve['points'])
                    ->filter(fn (array $point) => in_array($point['days'], ShopHydrateCustomerValueCurve::HORIZONS, true))
                    ->mapWithKeys(fn (array $point) => [array_search($point['days'], ShopHydrateCustomerValueCurve::HORIZONS, true) => [
                        'from'   => $point['cohort_from'],
                        'to'     => $point['cohort_to'],
                        'buyers' => $point['buyers'],
                    ]])
                    ->all(),
            ] : null,
            'channels'      => $channels->concat($paidChannelsWithoutCustomers)->all(),
            'cohorts'       => $this->cohorts($shop, $customers->filter(fn (array $customer) => $customer['registered_at'] >= $cohortStart->getTimestamp()), $cohortStart, $names, $now),
        ];
    }

    /**
     * @return Collection<int, array{id: int, registered_at: int, channel: string, orders: array<int, array{placed_at: int, net_amount: float, margin: float|null}>}>
     */
    private function customers(Shop $shop, Carbon $from, ?float $averageMargin): Collection
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

        $orders = DB::table('orders')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('customers.shop_id', $shop->id)
            ->whereNull('customers.deleted_at')
            ->where('customers.created_at', '>=', $from)
            ->whereNotIn('orders.state', [OrderStateEnum::CREATING->value, OrderStateEnum::CANCELLED->value])
            ->whereNull('orders.deleted_at')
            ->select('orders.id', 'orders.customer_id', 'orders.net_amount')
            ->selectRaw(GetShopOrderAttribution::ORDER_PLACED_AT.' as placed_at')
            ->get();

        $margins = $averageMargin !== null ? GetOrderGrossMargins::run($shop, $orders->pluck('id')->all(), $averageMargin) : [];

        $orders->each(function (object $order) use ($customers, $margins, $averageMargin) {
            $customer = $customers->get($order->customer_id);

            if (!$customer) {
                return;
            }

            $customer['orders'][] = [
                'placed_at'  => Carbon::parse($order->placed_at)->getTimestamp(),
                'net_amount' => (float) $order->net_amount,
                'margin'     => $averageMargin !== null ? $margins[$order->id] ?? 0.0 : null,
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
            'value'         => $this->valueFigures($customers, $buyers > 0 && $spend > 0 ? $spend / $buyers : null, $now),
        ];
    }

    /**
     * Gross margin per buyer from their first order, projected over each horizon with the shop's
     * value curve: what a buyer earned in the longest base window they have completed (7 to 90
     * days), times how the shop's past buyers grew from that base. A buyer younger than 7 days
     * counts with the margin so far over the 7-day ratio, so very young channels read low.
     * Payback is when the projected margin per buyer covers the cost per buyer.
     *
     * @param Collection<int, array{orders: array<int, array{placed_at: int, margin: float|null}>}> $customers
     *
     * @return array{buyers: int, margin_to_date: float|null, cltv: array<int, float|null>, ltv_cac: array<int, float|null>, payback_months: float|null, payback_beyond: bool}|null
     */
    private function valueFigures(Collection $customers, ?float $cac, int $now): ?array
    {
        if ($this->ratios === null) {
            return null;
        }

        $buyers     = $customers->filter(fn (array $customer) => !empty($customer['orders']));
        $cumulative = array_fill_keys(ShopHydrateCustomerValueCurve::POINT_DAYS, 0.0);
        $toDate     = 0.0;

        foreach ($buyers as $buyer) {
            $firstAt = min(array_column($buyer['orders'], 'placed_at'));
            $ageDays = ($now - $firstAt) / self::DAY;
            $base    = collect(ShopHydrateCustomerValueCurve::BASE_DAYS)->last(fn (int $days) => $days <= $ageDays) ?? ShopHydrateCustomerValueCurve::BASE_DAYS[0];
            $toDate += array_sum(array_column($buyer['orders'], 'margin'));

            $marginWithin = fn (int $days) => array_sum(array_column(
                array_filter($buyer['orders'], fn (array $order) => $order['placed_at'] < $firstAt + $days * self::DAY),
                'margin'
            ));

            foreach (ShopHydrateCustomerValueCurve::POINT_DAYS as $days) {
                $cumulative[$days] += $days <= $base ? $marginWithin($days) : $marginWithin($base) * ($this->ratios[$days][$base] ?? 1.0);
            }
        }

        $count   = $buyers->count();
        $average = array_map(fn (float $margin) => $count > 0 ? $margin / $count : null, $cumulative);
        $cltv    = array_map(fn (int $days) => $average[$days] !== null ? round($average[$days], 2) : null, ShopHydrateCustomerValueCurve::HORIZONS);
        $payback = $this->paybackDays($average, $cac);

        return [
            'buyers'         => $count,
            'margin_to_date' => $count > 0 ? round($toDate / $count, 2) : null,
            'cltv'           => $cltv,
            'ltv_cac'        => array_map(fn (?float $value) => $value !== null && $cac ? round($value / $cac, 2) : null, $cltv),
            'payback_months' => $payback !== null ? round($payback / 30.4375, 1) : null,
            'payback_beyond' => $cac !== null && $count > 0 && $payback === null,
        ];
    }

    /**
     * @param array<int, float|null> $average cumulative margin per buyer by day
     */
    private function paybackDays(array $average, ?float $cac): ?float
    {
        if (!$cac) {
            return null;
        }

        [$previousDays, $previousMargin] = [0, 0.0];

        foreach ($average as $days => $margin) {
            if ($margin === null) {
                return null;
            }

            if ($margin >= $cac) {
                return $previousDays + ($cac - $previousMargin) / max($margin - $previousMargin, 0.01) * ($days - $previousDays);
            }

            [$previousDays, $previousMargin] = [$days, $margin];
        }

        return null;
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
    private function cohorts(Shop $shop, Collection $customers, Carbon $cohortStart, array $names, int $now): array
    {
        $spend  = $this->spendByMonth($shop, $cohortStart);
        $months = [];

        for ($month = $cohortStart->copy(); $month->lte(now()); $month->addMonth()) {
            $months[$month->format('Y-m')] = $month->translatedFormat('M Y');
        }

        $channels = $customers
            ->groupBy('channel')
            ->map(function (Collection $group, string $channel) use ($months, $names, $now, $spend) {
                $byMonth = $group->groupBy(fn (array $customer) => Carbon::createFromTimestamp($customer['registered_at'])->format('Y-m'));

                return [
                    'type'      => $channel,
                    'name'      => $this->channelName($channel, $names),
                    'customers' => $group->count(),
                    'is_paid'   => (bool) TrafficSourcesTypeEnum::tryFrom($channel)?->isPaid(),
                    'rows'      => collect($months)->map(function (string $label, string $key) use ($byMonth, $now, $spend, $channel) {
                        $monthCustomers = $byMonth->get($key, collect());
                        $monthSpend     = $spend[$channel][$key] ?? 0.0;
                        $buyers         = $monthCustomers->filter(fn (array $customer) => !empty($customer['orders']))->count();
                        $cac            = $buyers > 0 && $monthSpend > 0 ? $monthSpend / $buyers : null;

                        return [
                            'key'       => $key,
                            'label'     => $label,
                            'customers' => $monthCustomers->count(),
                            'buyers'    => $buyers,
                            'spend'     => round($monthSpend, 2),
                            'cac'       => $cac !== null ? round($cac, 2) : null,
                            'cells'     => $this->windowFigures($monthCustomers, $now),
                            'value'     => $this->valueFigures($monthCustomers, $cac, $now),
                        ];
                    })->reverse()->values()->all(),
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
     * @return array<string, array<string, float>> spend by channel type and month (Y-m)
     */
    private function spendByMonth(Shop $shop, Carbon $from): array
    {
        $spend = [];

        DB::table('traffic_source_costs')
            ->join('traffic_sources', 'traffic_sources.id', '=', 'traffic_source_costs.traffic_source_id')
            ->where('traffic_source_costs.shop_id', $shop->id)
            ->where('traffic_source_costs.date', '>=', $from->toDateString())
            ->groupBy('traffic_sources.type', DB::raw("to_char(traffic_source_costs.date, 'YYYY-MM')"))
            ->select('traffic_sources.type')
            ->selectRaw("to_char(traffic_source_costs.date, 'YYYY-MM') as month")
            ->selectRaw('SUM(traffic_source_costs.amount) as spend')
            ->get()
            ->each(function (object $row) use (&$spend) {
                $spend[$row->type][$row->month] = (float) $row->spend;
            });

        return $spend;
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
