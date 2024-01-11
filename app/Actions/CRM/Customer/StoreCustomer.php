<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Helpers\TaxNumber\StoreTaxNumber;
use App\Actions\OrgAction;
use App\Actions\Market\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Market\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCustomers;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Throwable;

class StoreCustomer extends OrgAction
{
    private bool $asAction     = false;
    public int $hydratorsDelay = 0;
    private bool $strict       = true;


    /**
     * @throws Throwable
     */
    public function handle(Shop $shop, array $modelData): Customer
    {
        $contactAddressData = Arr::get($modelData, 'contact_address');
        Arr::forget($modelData, 'contact_address');
        $deliveryAddressData = Arr::get($modelData, 'delivery_address');
        Arr::forget($modelData, 'delivery_address');
        $taxNumberData = Arr::get($modelData, 'tax_number');
        Arr::forget($modelData, 'tax_number');

        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);

        if ($shop->type == ShopTypeEnum::DROPSHIPPING) {
            $modelData['is_dropshipping'] = true;
        } elseif ($shop->type == ShopTypeEnum::FULFILMENT) {
            $modelData['is_fulfilment'] = true;
        }


        data_fill(
            $modelData,
            'status',
            Arr::get($shop->settings, 'registration_type', 'open') == 'approval-only'
                ?
                CustomerStatusEnum::PENDING_APPROVAL
                :
                CustomerStatusEnum::APPROVED
        );

        $customer = DB::transaction(function () use ($shop, $modelData) {
            /** @var Customer $customer */
            $customer = $shop->customers()->create($modelData);
            if ($customer->reference == null) {
                $reference = GetSerialReference::run(container: $shop, modelType: SerialReferenceModelEnum::CUSTOMER);
                $customer->update(
                    [
                        'reference' => $reference
                    ]
                );
            }
            $customer->stats()->create();
            if ($customer->is_fulfilment) {
                $customer->fulfilmentStats()->create();
            }

            return $customer;
        });


        StoreAddressAttachToModel::run($customer, $contactAddressData, ['scope' => 'contact']);
        $customer->location = $customer->getLocation();
        $customer->save();

        if ($deliveryAddressData) {
            StoreAddressAttachToModel::run($customer, $deliveryAddressData, ['scope' => 'delivery']);
        }


        if ($taxNumberData) {
            if (!Arr::get($taxNumberData, 'data.name')) {
                Arr::forget($taxNumberData, 'data.name');
            }

            if (!Arr::get($taxNumberData, 'data.address')) {
                Arr::forget($taxNumberData, 'data.address');
            }

            StoreTaxNumber::run(
                owner: $customer,
                modelData: $taxNumberData
            );
        }

        ShopHydrateCustomers::dispatch($customer->shop)->delay($this->hydratorsDelay);
        ShopHydrateCustomerInvoices::dispatch($customer->shop)->delay($this->hydratorsDelay);
        OrganisationHydrateCustomers::dispatch($customer->shop->organisation)->delay($this->hydratorsDelay);

        CustomerHydrateUniversalSearch::dispatch($customer);


        return $customer;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->slug}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'contact_name'             => ['nullable', 'string', 'max:255'],
            'company_name'             => ['nullable', 'string', 'max:255'],
            'email'                    => [
                'nullable',
                'email',
                new IUnique(
                    table: 'customers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                    ]
                ),
            ],
            'phone'                    => ['nullable', 'max:255'],
            'identity_document_number' => ['nullable', 'string'],
            'contact_website'          => ['nullable', 'string', 'max:255'],
            'contact_address'          => ['required', new ValidAddress()],
            'delivery_address'         => ['sometimes', 'required', new ValidAddress()],


            'timezone_id' => ['nullable', 'exists:timezones,id'],
            'language_id' => ['nullable', 'exists:languages,id'],
            'data'        => ['sometimes', 'array'],
            'source_id'   => ['sometimes', 'nullable', 'string'],
            'password'    =>
                [
                    'sometimes',
                    'required',
                    app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()
                ],

        ];

        if ($this->strict) {
            $strictRules = [
                'phone'           => ['nullable', 'phone:AUTO'],
                'contact_website' => ['nullable', 'active_url'],
            ];
            $rules       = array_merge($rules, $strictRules);
        }

        return $rules;
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
    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Customer
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    /**
     * @throws Throwable
     */
    public function action(Shop $shop, array $modelData): Customer
    {
        $this->asAction = true;
        $this->initialisationFromShop($shop, $modelData);
        return $this->handle($shop, $this->validatedData);
    }

    /**
     * @throws Throwable
     */
    public function asFetch(Shop $shop, array $modelData, int $hydratorsDelay = 60): Customer
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = false;
        $this->initialisationFromShop($shop, $modelData);
        return $this->handle($shop, $this->validatedData);
    }

    public function htmlResponse(Customer $customer): RedirectResponse
    {
        return Redirect::route('grp.crm.shops.show.customers.show', [$customer->shop->slug, $customer->slug]);
    }
}
