<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierPurchaseOrder;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Procurement\PurchaseOrder;
use Lorisleiva\Actions\ActionRequest;

class UpdateSupplierPurchaseOrder
{
    use WithActionUpdate;

    public function handle(PurchaseOrder $supplierPurchaseOrder, array $modelData): PurchaseOrder
    {
        return $this->update($supplierPurchaseOrder, $modelData, ['data']);
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }

    public function rules(): array
    {
        return [
            'number'        => ['required', 'numeric', 'unique:supplier_deliveries'],
            'date'          => ['required', 'date'],
            'currency_id'   => ['required', 'exists:currencies,id'],
            'exchange'      => ['required', 'numeric']
        ];
    }

    public function action(PurchaseOrder $supplierPurchaseOrder, array $objectData): PurchaseOrder
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($supplierPurchaseOrder, $validatedData);
    }

    public function asController(PurchaseOrder $supplierPurchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $request->validate();
        return $this->handle($supplierPurchaseOrder, $request->all());
    }


}
