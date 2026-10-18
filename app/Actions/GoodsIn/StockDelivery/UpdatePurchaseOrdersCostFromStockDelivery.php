<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Tue, 28 Jul 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\Procurement\PurchaseOrder\CalculatePurchaseOrderTotalAmounts;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePurchaseOrdersCostFromStockDelivery
{
    use AsAction;

    private const COST_FIELDS = [
        'cost_items',
        'cost_extra',
        'cost_shipping',
        'cost_duties',
        'cost_tax',
        'cost_total',
    ];

    public function handle(StockDelivery $stockDelivery): void
    {
        foreach ($stockDelivery->purchaseOrders as $purchaseOrder) {
            $this->updatePurchaseOrderCost($purchaseOrder);
        }
    }

    public function action(StockDelivery $stockDelivery): void
    {
        $this->handle($stockDelivery);
    }

    private function updatePurchaseOrderCost(PurchaseOrder $purchaseOrder): void
    {
        $stockDeliveries = $purchaseOrder->stockDeliveries()
            ->where('stock_deliveries.state', '!=', StockDeliveryStateEnum::CANCELLED)
            ->get();

        $costingStockDeliveries = $stockDeliveries->where('state', StockDeliveryStateEnum::PLACED);

        if ($costingStockDeliveries->isEmpty()) {
            $purchaseOrder->update(['is_costed' => false]);
            CalculatePurchaseOrderTotalAmounts::run($purchaseOrder);

            return;
        }

        $purchaseOrder->update(array_merge(
            ['is_costed' => $stockDeliveries->where('is_costed', true)->count() === $stockDeliveries->count()],
            $this->getCosts($purchaseOrder, $costingStockDeliveries)
        ));
    }

    /**
     * A delivery that follows a supplier invoice in another currency than its purchase order is added in the
     * currency of the order, through what both are worth in the organisation's currency.
     */
    private function getCosts(PurchaseOrder $purchaseOrder, Collection $stockDeliveries): array
    {
        $costs = [];

        foreach (self::COST_FIELDS as $field) {
            $costs[$field] = $stockDeliveries->sum(fn (StockDelivery $stockDelivery) => (float) $stockDelivery->{$field} * $this->exchangeToPurchaseOrder($stockDelivery, $purchaseOrder));
        }

        return $costs;
    }

    private function exchangeToPurchaseOrder(StockDelivery $stockDelivery, PurchaseOrder $purchaseOrder): float
    {
        if ($stockDelivery->currency_id == $purchaseOrder->currency_id || (float) $stockDelivery->org_exchange <= 0 || (float) $purchaseOrder->org_exchange <= 0) {
            return 1.0;
        }

        return (float) $stockDelivery->org_exchange / (float) $purchaseOrder->org_exchange;
    }
}
