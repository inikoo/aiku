<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithStockDeliveryCostingEditAuthorisation;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryCostTypeEnum;
use App\Models\GoodsIn\StockDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * A delivery can carry shipping, duty or extra costs that no row of its costing checklist stands behind: an
 * estimate copied from the purchase order, or a split typed before the checklist existed. Removing them leaves
 * the delivery with what the checklist says, which is nothing until an invoice is entered.
 */
class RemoveStockDeliveryUnbackedCosts extends OrgAction
{
    use WithStockDeliveryCostingEditAuthorisation;

    private StockDelivery $stockDelivery;

    public static function hasUnbackedCosts(StockDelivery $stockDelivery): bool
    {
        return !$stockDelivery->is_costed
            && (float) $stockDelivery->cost_extra + (float) $stockDelivery->cost_shipping + (float) $stockDelivery->cost_duties > 0
            && !$stockDelivery->costs()
                ->whereIn('type', [StockDeliveryCostTypeEnum::SHIPPING, StockDeliveryCostTypeEnum::DUTY, StockDeliveryCostTypeEnum::EXTRA])
                ->where('is_na', false)
                ->where('amount', '>', 0)
                ->exists();
    }

    public function afterValidator(Validator $validator): void
    {
        if (!self::hasUnbackedCosts($this->stockDelivery)) {
            $validator->errors()->add('costs', __('The costs of this stock delivery come from its costing checklist, change them there'));
        }
    }

    public function handle(StockDelivery $stockDelivery): StockDelivery
    {
        $stockDelivery->items()->update(['cost_extra' => 0, 'cost_shipping' => 0, 'cost_duties' => 0]);

        return EvaluateStockDeliveryCosting::run($stockDelivery);
    }

    public function asController(StockDelivery $stockDelivery, ActionRequest $request): StockDelivery
    {
        $this->stockDelivery = $stockDelivery;
        $this->initialisation($stockDelivery->organisation, $request);

        return $this->handle($stockDelivery);
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->back();
    }
}
