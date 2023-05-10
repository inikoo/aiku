<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery;

use App\Actions\Procurement\PurchaseOrder\Traits\HasHydrators;
use App\Actions\WithActionUpdate;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierDelivery;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateStateToCreatingSupplierDelivery
{
    use WithActionUpdate;
    use AsAction;
    use HasHydrators;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(SupplierDelivery $supplierDelivery): SupplierDelivery
    {
        $data = [
            'state' => SupplierDeliveryStateEnum::CREATING,
        ];

        if ($supplierDelivery->state == SupplierDeliveryStateEnum::DISPATCHED) {
            $supplierDelivery->items()->update($data);

            $data[$supplierDelivery->state->value . '_at'] = null;

            $supplierDelivery = $this->update($supplierDelivery, $data);

            $this->purchaseOrderHydrate($supplierDelivery);

            return $supplierDelivery;
        }

        throw ValidationException::withMessages(['status' => 'You can not change the status to creating']);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(SupplierDelivery $supplierDelivery): SupplierDelivery
    {
        return $this->handle($supplierDelivery);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function asController(SupplierDelivery $supplierDelivery): SupplierDelivery
    {
        return $this->handle($supplierDelivery);
    }
}
