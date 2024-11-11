<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\StockDeliveryItem;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\StockDeliveryItemResource;
use App\Models\Procurement\StockDeliveryItem;
use Lorisleiva\Actions\ActionRequest;

class UpdateStockDeliveryItem
{
    use WithActionUpdate;

    public function handle(StockDeliveryItem $stockDeliveryItem, array $modelData): StockDeliveryItem
    {
        return $this->update($stockDeliveryItem, $modelData, ['data']);
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }

    public function rules(): array
    {
        return [
            'unit_quantity' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'unit_price'    => ['sometimes', 'required', 'numeric'],
        ];
        if (!$this->strict) {
            $rules['state'] = ['sometimes','required', Rule::enum(StockDeliveryItemStateEnum::class)];
            $rules['unit_quantity_checked'] = ['sometimes', 'numeric', 'gte:0'];
            $rules['unit_quantity_placed'] = ['sometimes', 'numeric', 'gte:0'];
            $rules = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function action(StockDeliveryItem $stockDeliveryItem, array $modelData, int $hydratorsDelay = 0, bool $strict = true): StockDeliveryItem
    {

        $this->asAction      = true;
        $this->strict        = $strict;

        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisation($stockDeliveryItem->organisation, $modelData);

        return $this->handle($stockDeliveryItem, $this->validatedData);
    }

    public function asController(StockDeliveryItem $stockDeliveryItem, ActionRequest $request): StockDeliveryItem
    {
        $request->validate();
        return $this->handle($stockDeliveryItem, $request->all());
    }

    public function jsonResponse(StockDeliveryItem $stockDeliveryItem): StockDeliveryItemResource
    {
        return new StockDeliveryItemResource($stockDeliveryItem);
    }
}
