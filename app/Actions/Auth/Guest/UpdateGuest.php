<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest;

use App\Actions\WithActionUpdate;
use App\Enums\Marketing\Shop\ShopTypeEnum;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\Auth\Guest;
use App\Models\Sales\Customer;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateGuest
{
    use WithActionUpdate;

    public function handle(Guest $guest, array $modelData): Guest
    {
        return $this->update($guest, $modelData, [
            'data',
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        return [
            'name'                      => ['sometimes','required', 'string', 'max:255'],
            'email'                     => ['sometimes','nullable', 'email'],
            'phone'                     => ['sometimes','nullable', 'string'],
            'identity_document_number'  => ['sometimes','nullable', 'string'],
            'identity_document_type'    => ['sometimes','nullable', 'string'],
            'type'                      => ['sometimes', 'required', Rule::in(ShopTypeEnum::values())],

        ];
    }


    public function asController(Customer $customer, ActionRequest $request): Customer
    {
        $request->validate();

        return $this->handle($customer, $request->all());
    }


    public function jsonResponse(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }
}
