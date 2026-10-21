<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource\Attribution;

use App\Actions\CRM\TrafficSource\GetAttributionStartedAt;
use App\Actions\CRM\TrafficSource\GetAttributionWindow;
use App\Actions\CRM\TrafficSource\ParseTrafficSourceTouches;
use App\Enums\CRM\TrafficSource\AttributionModelEnum;
use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetShopOrderAttribution
{
    use AsAction;

    public const string ORDER_PLACED_AT = 'COALESCE(orders.submitted_at, orders.date)';

    /** @var array<string, array<string, array<string, float>>> */
    private array $credit = [];

    /** @var array<string, array{orders: float, revenue: float}> */
    private array $noTouch = [];

    /**
     * @return array{from: string|null, to: string|null, currency_code: string, window_days: int, models: array<int, array{key: string, label: string, description: string}>, totals: array{orders: int, first: int, repeat: int, revenue: float}, no_touch: array<string, array{orders: float, revenue: float}>, channels: array<int, array<string, mixed>>}
     */
    public function handle(Shop $shop, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $startedAt = GetAttributionStartedAt::run();

        if ($startedAt && (!$from || $from->lt($startedAt))) {
            $from = $startedAt;
        }

        $key = implode(':', ['marketing-v2-orders', implode(',', AttributionModelEnum::values()), $shop->id, $from?->timestamp ?? 0, $to?->timestamp ?? 0]);

        return Cache::remember($key, now()->addMinutes(10), fn () => $this->calculate($shop, $from, $to));
    }

    private function calculate(Shop $shop, ?Carbon $from, ?Carbon $to): array
    {
        $windowDays    = GetAttributionWindow::run($shop);
        $splitter      = SplitOrderAttribution::make();
        $orders        = $this->orders($shop, $from, $to);
        $this->credit  = [];
        $this->noTouch = [];

        foreach (AttributionModelEnum::cases() as $model) {
            $this->noTouch[$model->value] = ['orders' => 0.0, 'revenue' => 0.0];
        }

        $ordersByCustomer = $orders->groupBy('customer_id');

        foreach ($ordersByCustomer->keys()->chunk(1000) as $customerIds) {
            $customers = DB::table('customers')
                ->whereIn('id', $customerIds->all())
                ->select('id', 'traffic_sources', 'created_at')
                ->get();

            foreach ($customers as $customer) {
                $touches      = $splitter->usableTouches(ParseTrafficSourceTouches::run($customer->traffic_sources));
                $registeredAt = Carbon::parse($customer->created_at)->getTimestamp();

                foreach ($ordersByCustomer->get($customer->id, collect()) as $order) {
                    $this->creditOrder($splitter, $order, $touches, $registeredAt, $windowDays);
                }
            }
        }

        return $this->summary($shop, $from, $to, $orders, $windowDays);
    }

    private function creditOrder(SplitOrderAttribution $splitter, object $order, array $touches, int $registeredAt, int $windowDays): void
    {
        $placedAt  = Carbon::parse($order->placed_at)->getTimestamp();
        $revenue   = (float) $order->net_amount;
        $orderType = $order->is_first ? 'first' : 'repeat';

        foreach (AttributionModelEnum::cases() as $model) {
            $shares = $splitter->handle($touches, $registeredAt, $placedAt, $model, $windowDays);

            if (empty($shares)) {
                $this->noTouch[$model->value]['orders']  += 1;
                $this->noTouch[$model->value]['revenue'] += $revenue;

                continue;
            }

            foreach ($shares as $share) {
                $figures = $this->credit[$share['type']][$model->value] ?? ['orders' => 0.0, 'revenue' => 0.0, 'first' => 0.0, 'repeat' => 0.0];

                $figures['orders']   += $share['share'];
                $figures['revenue']  += $revenue * $share['share'];
                $figures[$orderType] += $share['share'];

                $this->credit[$share['type']][$model->value] = $figures;
            }
        }
    }

    private function summary(Shop $shop, ?Carbon $from, ?Carbon $to, Collection $orders, int $windowDays): array
    {
        $spend       = self::spendByType($shop, $from, $to);
        $names       = DB::table('traffic_sources')->where('shop_id', $shop->id)->pluck('name', 'type');
        $firstOrders = $orders->where('is_first', true)->count();

        $channels = collect(array_keys($this->credit + $spend))
            ->map(function (string $type) use ($spend, $names) {
                $row = [
                    'type'    => $type,
                    'name'    => $names[$type] ?? TrafficSourcesTypeEnum::labels()[$type] ?? $type,
                    'is_paid' => (bool) TrafficSourcesTypeEnum::tryFrom($type)?->isPaid(),
                    'spend'   => round($spend[$type] ?? 0, 2),
                ];

                foreach (AttributionModelEnum::cases() as $model) {
                    $figures            = $this->credit[$type][$model->value] ?? [];
                    $row[$model->value] = [
                        'orders'  => round($figures['orders'] ?? 0, 2),
                        'revenue' => round($figures['revenue'] ?? 0, 2),
                        'first'   => round($figures['first'] ?? 0, 2),
                        'repeat'  => round($figures['repeat'] ?? 0, 2),
                    ];
                }

                return $row;
            })
            ->sortBy(fn (array $row) => [
                -max(array_map(fn (AttributionModelEnum $model) => $row[$model->value]['orders'], AttributionModelEnum::cases())),
                -$row['spend'],
                $row['name'],
            ])
            ->values()
            ->all();

        return [
            'from'          => $from?->toDateString(),
            'to'            => $to?->toDateString(),
            'currency_code' => $shop->currency->code,
            'window_days'   => $windowDays,
            'models'        => array_map(fn (AttributionModelEnum $model) => [
                'key'         => $model->value,
                'label'       => $model->label(),
                'description' => AttributionModelEnum::descriptions()[$model->value],
            ], AttributionModelEnum::cases()),
            'totals'        => [
                'orders'  => $orders->count(),
                'first'   => $firstOrders,
                'repeat'  => $orders->count() - $firstOrders,
                'revenue' => round((float) $orders->sum('net_amount'), 2),
            ],
            'no_touch'      => array_map(fn (array $figures) => [
                'orders'  => round($figures['orders'], 2),
                'revenue' => round($figures['revenue'], 2),
            ], $this->noTouch),
            'channels'      => $channels,
        ];
    }

    private function orders(Shop $shop, ?Carbon $from, ?Carbon $to): Collection
    {
        $excludedStates = [OrderStateEnum::CREATING->value, OrderStateEnum::CANCELLED->value];

        return DB::table('orders')
            ->where('orders.shop_id', $shop->id)
            ->whereNotIn('orders.state', $excludedStates)
            ->whereNull('orders.deleted_at')
            ->whereNotNull('orders.customer_id')
            ->when($from, fn ($query) => $query->whereRaw(self::ORDER_PLACED_AT.' >= ?', [$from]))
            ->when($to, fn ($query) => $query->whereRaw(self::ORDER_PLACED_AT.' <= ?', [$to]))
            ->select('orders.id', 'orders.customer_id', 'orders.net_amount')
            ->selectRaw(self::ORDER_PLACED_AT.' as placed_at')
            ->selectRaw(
                'NOT EXISTS (SELECT 1 FROM orders earlier WHERE earlier.customer_id = orders.customer_id AND earlier.deleted_at IS NULL AND earlier.state NOT IN (?, ?) AND COALESCE(earlier.submitted_at, earlier.date) < '.self::ORDER_PLACED_AT.') as is_first',
                $excludedStates
            )
            ->get();
    }

    /**
     * @return array<string, float>
     */
    public static function spendByType(Shop $shop, ?Carbon $from, ?Carbon $to): array
    {
        return DB::table('traffic_source_costs')
            ->join('traffic_sources', 'traffic_sources.id', '=', 'traffic_source_costs.traffic_source_id')
            ->where('traffic_source_costs.shop_id', $shop->id)
            ->when($from, fn ($query) => $query->where('traffic_source_costs.date', '>=', $from->toDateString()))
            ->when($to, fn ($query) => $query->where('traffic_source_costs.date', '<=', $to->toDateString()))
            ->groupBy('traffic_sources.type')
            ->select('traffic_sources.type', DB::raw('SUM(traffic_source_costs.amount) as spend'))
            ->pluck('spend', 'type')
            ->map(fn ($spend) => (float) $spend)
            ->all();
    }
}
