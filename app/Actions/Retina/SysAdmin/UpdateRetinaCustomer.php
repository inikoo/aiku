<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-16h-18m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\SysAdmin;

use App\Actions\CRM\Customer\UpdateCustomer;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Models\CRM\Customer;
use App\Rules\Phone;
use App\Rules\ValidAddress;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaCustomer extends RetinaAction
{
    use WithActionUpdate;
    use WithModelAddressActions;

    public function handle(Customer $customer, array $modelData): Customer
    {
        return UpdateCustomer::run($customer, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $this->customer->id = $request->route()->parameter('customer')->id and $request->user()->is_root;
    }

    public function rules(): array
    {
        return [
            'contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'        => ['sometimes', 'nullable', 'email'],
            'phone'        => ['sometimes', 'nullable', new Phone()],
            'contact_address'      => ['sometimes', 'required', new ValidAddress()],
        ];
    }


    public function asController(Customer $customer, ActionRequest $request): Customer
    {
        $this->initialisation($request);

        return $this->handle($customer, $this->validatedData);
    }


}
