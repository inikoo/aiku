<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\Fulfilment\FulfilmentCustomer\StoreFulfilmentCustomerFromCustomer;
use App\Actions\Helpers\Address\ParseCountryID;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Helpers\TaxNumber\StoreTaxNumber;
use App\Actions\OrgAction;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCustomers;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCustomers;
use App\Actions\Traits\WithModelAddressActions;
use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsCommand;

class StoreCustomer extends OrgAction
{
    use AsCommand;
    use WithModelAddressActions;


    public function handle(Shop $shop, array $modelData): Customer
    {
        $contactAddressData = Arr::get($modelData, 'contact_address', []);
        Arr::forget($modelData, 'contact_address');
        $deliveryAddressData = Arr::get($modelData, 'delivery_address', []);
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


        if (!Arr::get($modelData, 'reference')) {
            data_set($modelData, 'reference', GetSerialReference::run(container: $shop, modelType: SerialReferenceModelEnum::CUSTOMER));
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


        /** @var Customer $customer */
        $customer = $shop->customers()->create($modelData);


        $customer->stats()->create();

        if($customer->is_dropshipping) {
            $customer->dropshippingStats()->create();
        }

        if ($customer->is_fulfilment) {
            StoreFulfilmentCustomerFromCustomer::run($customer, $shop, ['source_id' => $customer->source_id]);
        }

        $customer = $this->addAddressToModel(
            model: $customer,
            addressData: $contactAddressData,
            scope: 'contact'
        );
        $customer->refresh();

        if (Arr::get($shop->settings, 'delivery_address_link')) {
            $customer = $this->addLinkedAddress(
                model:$customer,
                scope: 'delivery',
                updateLocation: false,
                updateAddressField: 'delivery_address_id'
            );
        } elseif($deliveryAddressData) {
            $customer = $this->addAddressToModel(
                model: $customer,
                addressData: $deliveryAddressData,
                scope: 'delivery',
                updateLocation: false,
                updateAddressField: 'delivery_address_id'
            );
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
        GroupHydrateCustomers::dispatch($customer->group)->delay($this->hydratorsDelay);
        OrganisationHydrateCustomers::dispatch($customer->organisation)->delay($this->hydratorsDelay);

        CustomerHydrateUniversalSearch::dispatch($customer);


        return $customer;
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
            'reference'                => ['sometimes', 'string', 'max:16'],
            'state'                    => ['sometimes', Rule::enum(CustomerStateEnum::class)],
            'status'                   => ['sometimes', Rule::enum(CustomerStatusEnum::class)],
            'contact_name'             => ['nullable', 'string', 'max:255'],
            'company_name'             => ['nullable', 'string', 'max:255'],
            'email'                    => [
                'nullable',
                'string',
                'max:255',
                'exclude_unless:deleted_at,null',
                new IUnique(
                    table: 'customers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
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
            'created_at'  => ['sometimes', 'nullable', 'date'],
            'deleted_at'  => ['sometimes', 'nullable', 'date'],
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
                'email'           => [
                    'nullable',
                    'email',
                    new IUnique(
                        table: 'customers',
                        extraConditions: [
                            ['column' => 'shop_id', 'value' => $this->shop->id],
                            ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ]
                    ),
                ],
            ];
            $rules       = array_merge($rules, $strictRules);
        }

        return $rules;
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->get('contact_name') and !$this->get('company_name') and !$this->get('email')) {
            $validator->errors()->add('company_name', 'At least one of contact_name, company_name or email must be provided');
        }
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Customer
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Customer
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }

    public string $commandSignature = 'customer:create {shop} {--contact_name=} {--company_name=} {--email=} {--phone=}  {--contact_website=} {--country=} ';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;
        $this->strict   = true;

        try {
            $shop = Shop::where('slug', $command->argument('shop'))->firstOrFail();
        } catch (Exception) {
            $command->error('Shop not found');

            return 1;
        }

        $address = [
            'country_id' => $shop->country_id
        ];

        if ($command->option('country')) {
            $address['country_id'] = ParseCountryID::run($command->option('country'));
        }


        $modelData = [
            'contact_name'    => $command->option('contact_name'),
            'company_name'    => $command->option('company_name'),
            'email'           => $command->option('email'),
            'phone'           => $command->option('phone'),
            'contact_website' => $command->option('contact_website'),
            'contact_address' => $address
        ];

        $this->initialisationFromShop($shop, $modelData);

        $customer = $this->handle($shop, $this->validatedData);

        echo "Customer $customer->reference created 🎉"."\n";

        return 0;
    }

    public function htmlResponse(Customer $customer): RedirectResponse
    {
        return Redirect::route('grp.org.shops.show.crm.customers.show', [$customer->organisation->slug, $customer->shop->slug, $customer->slug]);
    }
}
