<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDeliveryItem;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Procurement\SupplierDeliveryItemResource;
use App\Models\Procurement\SupplierDeliveryItem;
use Lorisleiva\Actions\ActionRequest;

class UpdateSupplierDeliveryItem
{
    use WithActionUpdate;

    public function handle(SupplierDeliveryItem $supplierDeliveryItem, array $modelData): SupplierDeliveryItem
    {
        return $this->update($supplierDeliveryItem, $modelData, ['data']);
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

    public function asController(SupplierDeliveryItem $supplierDeliveryItem, ActionRequest $request): SupplierDeliveryItem
    {
        $request->validate();
        return $this->handle($supplierDeliveryItem, $request->all());
    }

    public function jsonResponse(SupplierDeliveryItem $supplierDeliveryItem): SupplierDeliveryItemResource
    {
        return new SupplierDeliveryItemResource($supplierDeliveryItem);
    }
}
