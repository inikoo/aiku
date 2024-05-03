<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery;

use App\Actions\Procurement\SupplierDelivery\Traits\HasSupplierDeliveryHydrators;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Models\Procurement\SupplierDelivery;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateStateToSettledSupplierDelivery
{
    use WithActionUpdate;
    use AsAction;
    use HasSupplierDeliveryHydrators;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(SupplierDelivery $supplierDelivery): SupplierDelivery
    {
        $data = [
            'state' => SupplierDeliveryStateEnum::SETTLED,
        ];

        if ($supplierDelivery->state === SupplierDeliveryStateEnum::CHECKED) {
            $data[$supplierDelivery->state->value . '_at'] = null;
            $data['settled_at']                            = now();

            $supplierDelivery = $this->update($supplierDelivery, $data);

            $this->runHydrators($supplierDelivery);

            return $supplierDelivery;
        }

        throw ValidationException::withMessages(['status' => 'You can not change the status to settled']);
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
