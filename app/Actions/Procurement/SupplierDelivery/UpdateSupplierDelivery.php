<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery;

use App\Actions\WithActionUpdate;
use App\Models\Procurement\SupplierDelivery;
use Lorisleiva\Actions\ActionRequest;

class UpdateSupplierDelivery
{
    use WithActionUpdate;

    public function handle(SupplierDelivery $supplierDelivery, array $modelData): SupplierDelivery
    {
        return $this->update($supplierDelivery, $modelData, ['data']);
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }

    public function rules(): array
    {
        return [
            'number'        => ['required', 'numeric', 'unique:group.supplier_deliveries'],
            'date'          => ['required', 'date'],
            'currency_id'   => ['required', 'exists:currencies,id'],
            'exchange'      => ['required', 'numeric']
        ];
    }

    public function action(SupplierDelivery $supplierDelivery, array $objectData): SupplierDelivery
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($supplierDelivery, $validatedData);
    }

    public function asController(SupplierDelivery $supplierDelivery, ActionRequest $request): SupplierDelivery
    {
        $request->validate();
        return $this->handle($supplierDelivery, $request->all());
    }


}
