<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrderItem;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrderItem;
use Lorisleiva\Actions\ActionRequest;

class UpdatePurchaseOrderItem
{
    use WithActionUpdate;

    public function handle(PurchaseOrderItem $purchaseOrderItem, array $modelData): PurchaseOrderItem
    {
        return $this->update($purchaseOrderItem, $modelData, ['data']);
    }

//    public function authorize(ActionRequest $request): bool
//    {
//        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
//    }

    public function rules(): array
    {
        return [
            'unit_quantity' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'unit_price' => ['sometimes', 'required', 'numeric'],
        ];
    }

    public function asController(PurchaseOrderItem $purchaseOrderItem, ActionRequest $request): PurchaseOrderItem
    {
        $request->validate();
        return $this->handle($purchaseOrderItem, $request->all());
    }

    public function jsonResponse(PurchaseOrderItem $purchaseOrderItem): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrderItem);
    }
}
