<?php

/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created: Tue, 13 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\Ordering\Order;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The GTM "purchase" event the customer's browser still owes for an order, or null once the
 * browser has confirmed it. The order page carries it instead of a session flash, so a missed
 * redirect, a refresh or another tab cannot lose it.
 */
class GetOrderPendingPurchaseGtmEvent
{
    use AsAction;

    public const int DAYS_AFTER_SUBMITTED = 7;

    /**
     * @return array{event: string, data_to_submit: array{ecommerce: array<string, mixed>}, confirm_route: array{name: string, parameters: array{order: int}}}|null
     */
    public function handle(Order $order): ?array
    {
        if (!$this->isPurchasePending($order)) {
            return null;
        }

        return [
            'event'          => 'purchase',
            'data_to_submit' => [
                'ecommerce' => GetOrderPurchaseEcommerceData::run($order)
            ],
            'confirm_route'  => [
                'name'       => 'retina.models.order.gtm_purchase_sent',
                'parameters' => [
                    'order' => $order->id
                ]
            ]
        ];
    }

    public function isPurchasePending(Order $order): bool
    {
        if (!$order->submitted_at || $order->state == OrderStateEnum::CANCELLED) {
            return false;
        }

        if ($order->submitted_at->lt(now()->subDays(self::DAYS_AFTER_SUBMITTED))) {
            return false;
        }

        if (data_get($order->data, 'gtm_purchase_sent_at')) {
            return false;
        }

        return !$order->platform_id || $order->platform?->type == PlatformTypeEnum::MANUAL;
    }
}
