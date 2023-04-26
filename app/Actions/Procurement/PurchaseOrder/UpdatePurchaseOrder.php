<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Supplier;
use Lorisleiva\Actions\ActionRequest;

class UpdatePurchaseOrder
{
    use WithActionUpdate;

    public function handle(PurchaseOrder $purchaseOrder, array $modelData): PurchaseOrder
    {
        return $this->update($purchaseOrder, $modelData, ['data']);
    }

//    public function authorize(ActionRequest $request): bool
//    {
//        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
//    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'numeric', 'unique:group.purchase_orders'],
            'provider_id' => ['required'],
            'provider_type' => ['required'],
            'date' => ['required', 'date'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'exchange' => ['required', 'numeric']
        ];
    }

    public function action(PurchaseOrder $purchaseOrder, array $objectData): PurchaseOrder
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($purchaseOrder, $validatedData);
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $request->validate();
        return $this->handle($purchaseOrder, $request->all());
    }

    public function jsonResponse(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }
}
