<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\SupplyChain\StockDeliveryInvoice\EstimateStockDeliveryInvoice;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryCostTypeEnum;
use App\Enums\SupplyChain\StockDeliveryInvoice\StockDeliveryInvoiceSourceEnum;
use App\Models\GoodsIn\StockDelivery;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * A supplier in the organisation's own country invoices after the goods arrive and has no freight bill or duty of
 * its own, so its delivery starts with shipping and duty not applicable and the supplier invoice estimated from its
 * lines. Nothing is missing for put-away, and the delivery stays not costed until the real invoice is entered.
 * Rows a person already made are left alone; the estimate is redone so it follows the lines added since.
 */
class PrefillDomesticStockDeliveryCosts
{
    use AsAction;

    public function handle(StockDelivery $stockDelivery): StockDelivery
    {
        if (!self::isDomestic($stockDelivery) || $stockDelivery->is_costed) {
            return $stockDelivery;
        }

        foreach ([StockDeliveryCostTypeEnum::SHIPPING, StockDeliveryCostTypeEnum::DUTY] as $type) {
            if (!$stockDelivery->costs()->where('type', $type)->exists()) {
                StoreStockDeliveryCost::make()->action($stockDelivery, ['type' => $type->value, 'is_na' => true]);
            }
        }

        if (!$stockDelivery->hasNoProducts()) {
            EvaluateStockDeliveryCosting::run($stockDelivery);
            $stockDelivery->supplierInvoice()->where('source', StockDeliveryInvoiceSourceEnum::ESTIMATED)->delete();
            EstimateStockDeliveryInvoice::run($stockDelivery);
        }

        return $stockDelivery;
    }

    public static function isDomestic(StockDelivery $stockDelivery): bool
    {
        if ($stockDelivery->parent_type !== 'OrgSupplier' || $stockDelivery->agent_id || $stockDelivery->source_id) {
            return false;
        }

        $supplierCountryId = $stockDelivery->parent?->supplier?->address?->country_id;

        return $supplierCountryId !== null && (int) $supplierCountryId === (int) $stockDelivery->organisation->country_id;
    }
}
