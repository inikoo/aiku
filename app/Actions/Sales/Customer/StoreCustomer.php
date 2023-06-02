<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateCustomers;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Enums\Marketing\Shop\ShopSubtypeEnum;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Validation\Validator;
use Throwable;

class StoreCustomer
{
    use AsAction;
    use WithAttributes;

    private bool $asAction     = false;
    public int $hydratorsDelay = 0;


    /**
     * @throws Throwable
     */
    public function handle(Shop $shop, array $customerData, array $customerAddressesData = []): Customer
    {
        if ($shop->subtype == ShopSubtypeEnum::DROPSHIPPING) {
            $customerData['is_dropshipping'] = true;
        } elseif ($shop->subtype == ShopSubtypeEnum::FULFILMENT) {
            $customerData['is_fulfilment'] = true;
        }


        data_fill(
            $customerData,
            'status',
            Arr::get($shop->settings, 'registration_type', 'open') == 'approval-only'
                ?
                CustomerStatusEnum::PENDING_APPROVAL
                :
                CustomerStatusEnum::APPROVED
        );

        $customer = DB::transaction(function () use ($shop, $customerData) {
            /** @var Customer $customer */
            $customer = $shop->customers()->create($customerData);
            if ($customer->reference == null) {
                $reference = GetSerialReference::run(container: $shop, modelType: SerialReferenceModelEnum::CUSTOMER);
                $customer->update(
                    [
                        'reference' => $reference
                    ]
                );
            }
            $customer->stats()->create();

            return $customer;
        });


        StoreAddressAttachToModel::run($customer, $customerAddressesData, ['scope' => 'contact']);
        $customer->location = $customer->getLocation();
        $customer->save();


        ShopHydrateCustomers::dispatch($customer->shop)->delay($this->hydratorsDelay);
        ShopHydrateCustomerInvoices::dispatch($customer->shop)->delay($this->hydratorsDelay);
        TenantHydrateCustomers::dispatch(app('currentTenant'))->delay($this->hydratorsDelay);
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
            'contact_name'             => ['nullable', 'string', 'max:255'],
            'company_name'             => ['nullable', 'string', 'max:255'],
            'email'                    => ['nullable', 'email'],
            'phone'                    => ['nullable', 'string'],
            'identity_document_number' => ['nullable', 'string'],
            //'website'                   => ['nullable', 'active_url'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->get('contact_name') and !$this->get('company_name')) {
            $validator->errors()->add('company_name', 'contact name or company name required');
        }
    }

    /**
     * @throws Throwable
     */
    public function asController(Shop $shop, ActionRequest $request): Customer
    {
        $this->fillFromRequest($request);
        $request->validate();

        return $this->handle($shop, $request->validated());
    }

    public function htmlResponse(Customer $customer): RedirectResponse
    {
        return Redirect::route('shops.show.customers.show', [$customer->shop->slug, $customer->slug]);
    }


    /**
     * @throws Throwable
     */
    public function action(Shop $shop, array $objectData, array $customerAddressesData): Customer
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData, $customerAddressesData);
    }

    /**
     * @throws Throwable
     */
    public function asFetch(Shop $shop, array $customerData, array $customerAddressesData, int $hydratorsDelay = 60): Customer
    {
        $this->hydratorsDelay = $hydratorsDelay;

        return $this->handle($shop, $customerData, $customerAddressesData);
    }
}
