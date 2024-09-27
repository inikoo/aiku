<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 27 Sept 2024 11:46:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateClients;
use App\Actions\Dropshipping\CustomerClient\Search\CustomerClientRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithModelAddressActions;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Rules\IUnique;
use App\Rules\Phone;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;

class StoreCustomerClient extends OrgAction
{
    use WithModelAddressActions;


    /**
     * @throws \Throwable
     */
    public function handle(Customer $customer, array $modelData): CustomerClient
    {
        $address = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');

        data_set($modelData, 'ulid', Str::ulid());
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'shop_id', $customer->shop_id);


        $customerClient = DB::transaction(function () use ($customer, $modelData, $address) {
            /** @var CustomerClient $customerClient */
            $customerClient = $customer->clients()->create($modelData);
            $customerClient->stats()->create();

            return $this->addAddressToModel(
                model: $customerClient,
                addressData: $address,
                scope: 'delivery',
                canShip: true
            );
        });


        CustomerClientRecordSearch::dispatch($customerClient);
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

            'reference'      => ['sometimes','nullable', 'string', 'max:255',
                                 new IUnique(
                                     table: 'customer_clients',
                                     extraConditions: [
                                         ['column' => 'customer_id', 'value' => $this->shop->id],
                                     ]
                                 ),
                ],
            'contact_name'   => ['nullable', 'string', 'max:255'],
            'company_name'   => ['nullable', 'string', 'max:255'],
            'email'          => ['nullable', 'email'],
            'phone'          => ['nullable', new Phone()],
            'address'        => ['required', new ValidAddress()],
            'deactivated_at' => ['sometimes', 'nullable', 'date'],
            'status'         => ['sometimes', 'boolean'],

        ];

        if (!$this->strict) {
            $rules['deleted_at'] = ['sometimes', 'nullable', 'date'];
            $rules['created_at'] = ['sometimes', 'nullable', 'date'];
            $rules['fetched_at'] = ['sometimes', 'date'];
            $rules['source_id']  = ['sometimes', 'string', 'max:255'];
            $rules['email']      = ['sometimes', 'nullable', 'string', 'max:255'];
            $rules['phone']      = ['sometimes', 'nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function htmlResponse(Customer $customer): RedirectResponse
    {
        return Redirect::route('grp.org.shops.show.crm.customers.show.customer-clients.index', [$customer->organisation->slug, $customer->shop->slug, $customer->slug]);
    }

    /**
     * @throws \Throwable
     */
    public function action(Customer $customer, array $modelData, int $hydratorsDelay = 0, bool $strict = true): CustomerClient
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $this->validatedData);
    }

    /**
     * @throws \Throwable
     */
    public function asController(Customer $customer, ActionRequest $request): CustomerClient
    {
        $this->asAction = true;
        $this->initialisationFromShop($customer->shop, $request);

        return $this->handle($customer, $this->validatedData);
    }


}
