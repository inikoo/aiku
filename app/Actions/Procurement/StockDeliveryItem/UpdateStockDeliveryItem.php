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
