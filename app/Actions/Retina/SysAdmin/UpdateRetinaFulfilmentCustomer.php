<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-16h-18m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\SysAdmin;

use App\Actions\CRM\Customer\UpdateCustomer;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Models\Fulfilment\FulfilmentCustomer;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaFulfilmentCustomer extends RetinaAction
{
    use WithActionUpdate;
    use WithModelAddressActions;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): FulfilmentCustomer
    {
        $customerData       = Arr::only($modelData, ['contact_name', 'company_name', 'email', 'phone']);
        $contactAddressData = Arr::get($modelData, 'address');
        UpdateCustomer::run($fulfilmentCustomer->customer, $customerData);

        if (! blank($contactAddressData)) {
            if ($fulfilmentCustomer->customer->address) {
                UpdateAddress::run($fulfilmentCustomer->customer->address, $contactAddressData);
            } else {
                $this->addAddressToModelFromArray(
                    model: $fulfilmentCustomer->customer,
                    addressData: $contactAddressData,
                    scope: 'billing',
                    updateLocation: false,
                    canShip: true
                );
            }
        }

        return $fulfilmentCustomer;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->is_root;
    }

    public function rules(): array
    {
        return [
            'contact_name'    => ['sometimes', 'nullable','string'],
            'company_name'    => ['sometimes', 'nullable','string'],
            'email'           => ['sometimes', 'nullable','string'],
            'phone'           => ['sometimes', 'nullable','string'],
            'address'         => ['sometimes'],
        ];
    }


    public function asController(
        FulfilmentCustomer $fulfilmentCustomer,
        ActionRequest $request
    ): FulfilmentCustomer {

        $this->initialisation($request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


}
