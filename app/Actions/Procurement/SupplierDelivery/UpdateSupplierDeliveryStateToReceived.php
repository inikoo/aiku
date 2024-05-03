<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDelivery;

use App\Actions\OrgAction;
use App\Actions\Procurement\SupplierDelivery\Traits\HasSupplierDeliveryHydrators;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Models\Procurement\SupplierDelivery;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class UpdateSupplierDeliveryStateToReceived extends OrgAction
{
    use WithActionUpdate;
    use HasSupplierDeliveryHydrators;


    private SupplierDelivery $supplierDelivery;

    public function handle(SupplierDelivery $supplierDelivery): SupplierDelivery
    {
        $data = [
            'state' => SupplierDeliveryStateEnum::RECEIVED,
        ];

        $data[$supplierDelivery->state->value.'_at'] = null;
        $data['received_at']                         = now();

        $supplierDelivery = $this->update($supplierDelivery, $data);

        $this->runHydrators($supplierDelivery);

        return $supplierDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");
    }

    public function afterValidator(Validator $validator): void
    {
        if (!in_array($this->supplierDelivery->state, [SupplierDeliveryStateEnum::CREATING, SupplierDeliveryStateEnum::DISPATCHED,SupplierDeliveryStateEnum::CHECKED])) {
            $validator->errors()->add('state', __('You can not change the status to received if state is'.' '.$this->supplierDelivery->state->value));
        }
    }

    public function action(SupplierDelivery $supplierDelivery): SupplierDelivery
    {
        $this->asAction         = true;
        $this->supplierDelivery = $supplierDelivery;
        $this->initialisation($supplierDelivery->organisation, []);

        return $this->handle($supplierDelivery);
    }


    public function asController(SupplierDelivery $supplierDelivery): SupplierDelivery
    {
        return $this->handle($supplierDelivery);
    }
}
