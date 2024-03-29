<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use Lorisleiva\Actions\ActionRequest;

class UpdatePurchaseOrder
{
    use WithActionUpdate;

    public function handle(PurchaseOrder $purchaseOrder, array $modelData): PurchaseOrder
    {
        $purchaseOrder = $this->update($purchaseOrder, $modelData, ['data']);
        HydratePurchaseOrder::dispatch($purchaseOrder);

        return $purchaseOrder;
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }

    public function rules(): array
    {
        return [
            'number'        => ['sometimes', 'numeric', 'unique:purchase_orders'],
            'date'          => ['sometimes', 'date'],
            'currency_id'   => ['sometimes', 'exists:currencies,id'],
        ];
    }

    public function action(PurchaseOrder $purchaseOrder, array $modelData): PurchaseOrder
    {
        $this->setRawAttributes($modelData);
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
