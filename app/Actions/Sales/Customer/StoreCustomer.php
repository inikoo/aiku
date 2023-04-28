<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateCustomers;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Support\Facades\Bus;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Validation\Validator;

class StoreCustomer
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(Shop $shop, array $customerData, array $customerAddressesData = []): Customer
    {
        /** @var Customer $customer */
        $customer = $shop->customers()->create($customerData);
        $customer->stats()->create();

        StoreAddressAttachToModel::run($customer, $customerAddressesData, ['scope' => 'contact']);
        $customer->location = $customer->getLocation();
        $customer->save();


        Bus::chain([
            ShopHydrateCustomers::makeJob($customer->shop),
            ShopHydrateCustomerInvoices::makeJob($customer->shop)
        ])->dispatch();



        TenantHydrateCustomers::dispatch(app('currentTenant'));

        CustomerHydrateUniversalSearch::dispatch($customer);

        return $customer;
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
            'contact_name'              => ['nullable', 'string', 'max:255'],
            'company_name'              => ['nullable', 'string', 'max:255'],
            'email'                     => ['nullable', 'email'],
            'phone'                     => ['nullable', 'string'],
            'identity_document_number'  => ['nullable', 'string'],
            'website'                   => ['nullable', 'active_url'],
        ];
    }

    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if (!$this->get('contact_name') and !$this->get('company_name')) {
            $validator->errors()->add('contact_name', 'contact name required x');
        }
    }

    public function action(Shop $shop, array $objectData): Customer
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }
}
