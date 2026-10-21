<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\TrafficSource\Attribution;

use App\Actions\CRM\TrafficSource\GetAttributionWindow;
use App\Actions\CRM\TrafficSource\ParseTrafficSourceTouches;
use App\Enums\CRM\TrafficSource\AttributionModelEnum;
use App\Enums\CRM\TrafficSource\TrafficSourcesTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Ordering\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetShopOrderJourney
{
    use AsAction;

    private const int RECENT_TOUCHES = 10;

    private const int OTHER_ORDERS = 4;

    /**
     * @return array<string, mixed>
     */
    public function handle(Shop $shop, ?string $reference): array
    {
        $reference = trim((string) $reference);

        $result = [
            'query'         => $reference,
            'found'         => false,
            'currency_code' => $shop->currency->code,
            'rules'         => [
                'models'                  => array_map(fn (AttributionModelEnum $model) => [
                    'key'         => $model->value,
                    'label'       => $model->label(),
                    'description' => AttributionModelEnum::descriptions()[$model->value],
                ], AttributionModelEnum::cases()),
                'window_days'             => GetAttributionWindow::run($shop),
                'acquisition_range_days'  => SplitOrderAttribution::ACQUISITION_RANGE_DAYS,
                'new_customer_window'     => SplitOrderAttribution::NEW_CUSTOMER_WINDOW_DAYS,
                'existing_customer_window' => SplitOrderAttribution::EXISTING_CUSTOMER_WINDOW_DAYS,
            ],
        ];

        if ($reference === '') {
            return $result;
        }

        /** @var Order|null $order */
        $order = Order::where('shop_id', $shop->id)
            ->whereIn('reference', array_unique([$reference, strtoupper($reference)]))
            ->first();

        if (!$order || !$order->customer) {
            return $result;
        }

        return array_merge($result, ['found' => true], $this->journey($shop, $order->customer, $order));
    }

    /**
     * @return array<string, mixed>
     */
    private function journey(Shop $shop, Customer $customer, Order $selected): array
    {
        $splitter     = SplitOrderAttribution::make();
        $touches      = $splitter->usableTouches(ParseTrafficSourceTouches::run($customer->traffic_sources));
        $registeredAt = $customer->created_at->getTimestamp();
        $acquisition  = $splitter->acquisitionTouch($touches, $registeredAt);
        $windowDays   = GetAttributionWindow::run($shop);
        $names        = DB::table('traffic_sources')->where('shop_id', $shop->id)->pluck('name', 'type')->all();
        $campaigns    = DB::table('traffic_source_campaigns')
            ->whereIn('reference', collect($touches)->pluck('campaign_ref')->filter()->unique()->values()->all())
            ->pluck('name', 'reference')
            ->all();

        $orders = Order::where('customer_id', $customer->id)
            ->whereNotIn('state', [OrderStateEnum::CREATING, OrderStateEnum::CANCELLED])
            ->select('id', 'slug', 'reference', 'net_amount', 'submitted_at', 'date', 'state')
            ->get()
            ->map(fn (Order $order) => [
                'order'     => $order,
                'placed_at' => ($order->submitted_at ?? $order->date)->getTimestamp(),
            ])
            ->sortBy('placed_at')
            ->values();

        $firstOrderId = $orders->first()['order']->id ?? null;

        $shown = $orders
            ->reject(fn (array $row) => $row['order']->id === $selected->id)
            ->sortByDesc('placed_at')
            ->take(self::OTHER_ORDERS)
            ->prepend($orders->firstWhere(fn (array $row) => $row['order']->id === $selected->id) ?? [
                'order'     => $selected,
                'placed_at' => ($selected->submitted_at ?? $selected->date)->getTimestamp(),
            ])
            ->values();

        $channelName = fn (string $type) => $names[$type] ?? TrafficSourcesTypeEnum::labels()[$type] ?? $type;

        return [
            'customer' => [
                'name'          => $customer->name,
                'reference'     => $customer->reference,
                'registered_at' => $customer->created_at->toIso8601String(),
                'acquisition'   => $acquisition ? [
                    'type' => $acquisition['type']->value,
                    'name' => $channelName($acquisition['type']->value),
                    'date' => Carbon::createFromTimestamp($acquisition['timestamp'])->toIso8601String(),
                ] : null,
                'orders'        => $orders->count(),
                'orders_value'  => round((float) $orders->sum(fn (array $row) => $row['order']->net_amount), 2),
                'route'         => [
                    'name'       => 'grp.org.shops.show.crm.customers.show',
                    'parameters' => [$shop->organisation->slug, $shop->slug, $customer->slug],
                ],
            ],
            'orders'   => $shown->map(fn (array $row) => $this->orderJourney(
                $shop,
                $row['order'],
                $row['placed_at'],
                $row['order']->id === $selected->id,
                $row['order']->id === $firstOrderId,
                $touches,
                $registeredAt,
                $acquisition,
                $windowDays,
                $channelName,
                $campaigns
            ))->all(),
        ];
    }

    /**
     * @param array<int, array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}> $touches
     * @param array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null}|null $acquisition
     * @param array<string, string> $campaigns
     *
     * @return array<string, mixed>
     */
    private function orderJourney(Shop $shop, Order $order, int $placedAt, bool $isSelected, bool $isFirst, array $touches, int $registeredAt, ?array $acquisition, int $windowDays, callable $channelName, array $campaigns): array
    {
        $splitter = SplitOrderAttribution::make();
        $shares   = [];
        $splits   = [];

        foreach (AttributionModelEnum::cases() as $model) {
            $credits = $splitter->handle($touches, $registeredAt, $placedAt, $model, $windowDays);

            foreach ($credits as $credit) {
                $shares[$credit['timestamp'].'|'.$credit['type'].'|'.$credit['campaign_ref']][$model->value] = $credit['share'];
            }

            $splits[$model->value] = collect($credits)
                ->groupBy('type')
                ->map(fn ($group, string $type) => ['type' => $type, 'name' => $channelName($type), 'share' => round($group->sum('share'), 4)])
                ->sortByDesc('share')
                ->values()
                ->all();
        }

        $before   = array_values(array_filter($touches, fn (array $touch) => $touch['timestamp'] <= $placedAt));
        $credited = fn (array $touch) => isset($shares[$this->touchKey($touch)]);
        $isAcquisition = fn (array $touch) => $acquisition && $touch['timestamp'] === $acquisition['timestamp'] && $touch['type'] === $acquisition['type'];

        $keep = [];

        foreach ($before as $index => $touch) {
            if ($credited($touch) || $isAcquisition($touch) || $index >= count($before) - self::RECENT_TOUCHES) {
                $keep[] = $touch;
            }
        }

        return [
            'reference'   => $order->reference,
            'placed_at'   => Carbon::createFromTimestamp($placedAt)->toIso8601String(),
            'net_amount'  => (float) $order->net_amount,
            'is_selected' => $isSelected,
            'is_first'    => $isFirst,
            'route'       => [
                'name'       => 'grp.org.shops.show.ordering.orders.show',
                'parameters' => [$shop->organisation->slug, $shop->slug, $order->slug],
            ],
            'omitted_touches' => count($before) - count($keep),
            'touches'     => array_map(fn (array $touch) => [
                'date'           => Carbon::createFromTimestamp($touch['timestamp'])->toIso8601String(),
                'type'           => $touch['type']->value,
                'name'           => $channelName($touch['type']->value),
                'campaign'       => $touch['campaign_ref'] ? ($campaigns[$touch['campaign_ref']] ?? $touch['campaign_ref']) : null,
                'is_acquisition' => $isAcquisition($touch),
                'shares'         => array_map(
                    fn (AttributionModelEnum $model) => $shares[$this->touchKey($touch)][$model->value] ?? null,
                    array_combine(AttributionModelEnum::values(), AttributionModelEnum::cases())
                ),
            ], $keep),
            'splits'      => $splits,
        ];
    }

    /**
     * @param array{timestamp: int, type: TrafficSourcesTypeEnum, campaign_ref: string|null} $touch
     */
    private function touchKey(array $touch): string
    {
        return $touch['timestamp'].'|'.$touch['type']->value.'|'.$touch['campaign_ref'];
    }
}
