<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\Sales\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\Sales\Customer;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateCustomer
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(Customer $customer, array $modelData): Customer
    {
        if (Arr::hasAny($modelData, ['contact_name', 'company_name'])) {
            $contact_name = Arr::exists($modelData, 'contact_name') ? Arr::get($modelData, 'contact_name') : $customer->contact_name;
            $company_name = Arr::exists($modelData, 'company_name') ? Arr::get($modelData, 'company_name') : $customer->company_name;

            $modelData['name'] = $company_name ?: $contact_name;
        }

        $customer = $this->update($customer, $modelData, ['data']);


        CustomerHydrateUniversalSearch::dispatch($customer);

        return $customer;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        return [
            'contact_name'             => ['sometimes'],
            'company_name'             => ['sometimes'],
            'phone'                    => 'sometimes|phone:AUTO',
            'identity_document_number' => ['sometimes', 'nullable', 'string'],
            'contact_website'          => ['sometimes', 'nullable', 'active_url'],
            'email'                    => ['sometimes', 'nullable', 'email'],

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
