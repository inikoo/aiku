<?php
/*
 * author Arya Permana - Kirin
 * created on 17-01-2025-09h-19m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\CRM;

use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaCustomerDeliveryAddress extends RetinaAction
{
    use WithActionUpdate;

    public function handle(Customer $customer, array $modelData): Customer
    {
        if (isset($modelData['delivery_address_id'])) {
            $customer->delivery_address_id = $modelData['delivery_address_id'];
            $customer->save();
        }
        return $customer;
    }

    public function rules(): array
    {
        $rules = [
            'delivery_address_id'         => ['sometimes', 'nullable', 'exists:addresses,id'],
        ];

        return $rules;
    }

    public function fromRetina(Customer $customer, ActionRequest $request): Customer
    {
        $customer = $request->user()->customer;

        $this->initialisation($request);

        return $this->handle($customer, $this->validatedData);
    }
}
