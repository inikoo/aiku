<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:03:02 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateClients;
use App\Actions\Dropshipping\CustomerClient\Hydrators\CustomerClientHydrateUniversalSearch;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class StoreCustomerClient extends OrgAction
{
    public function handle(Customer $customer, array $modelData): CustomerClient
    {
        $deliveryAddressData = Arr::get($modelData, 'delivery_address');
        Arr::forget($modelData, 'delivery_address');


        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'shop_id', $customer->shop_id);


        /** @var CustomerClient $customerClient */
        $customerClient = $customer->clients()->create($modelData);


        StoreAddressAttachToModel::run($customerClient, $deliveryAddressData, ['scope' => 'delivery']);
        CustomerClientHydrateUniversalSearch::dispatch($customerClient);
        CustomerHydrateClients::dispatch($customer);

        return $customerClient;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        $rules = [

            'reference'        => ['nullable', 'string', 'max:255'],
            'contact_name'     => ['nullable', 'string', 'max:255'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'email'            => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:255'],
            'delivery_address' => ['required', new ValidAddress()],
            'source_id'        => 'sometimes|nullable|string|max:255',
            'created_at'       => 'sometimes|nullable|date',
            'deactivated_at'   => 'sometimes|nullable|date',
            'status'           => ['sometimes', 'boolean'],

        ];

        if ($this->strict) {
            $strictRules = [
                'phone' => ['nullable', 'phone:AUTO'],
                'email' => [
                    'nullable',
                    'email',
                ],
            ];
            $rules       = array_merge($rules, $strictRules);
        }

        return $rules;
    }

    public function action(Customer $customer, array $modelData): CustomerClient
    {
        $this->asAction = true;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $this->validatedData);
    }

    public function asFetch(Customer $customer, array $modelData): CustomerClient
    {
        $this->asAction = true;
        $this->strict   = false;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $this->validatedData);
    }
}
