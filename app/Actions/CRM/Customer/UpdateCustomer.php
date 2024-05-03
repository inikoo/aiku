<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateUniversalSearch;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Helpers\TaxNumber\DeleteTaxNumber;
use App\Actions\Helpers\TaxNumber\StoreTaxNumber;
use App\Actions\Helpers\TaxNumber\UpdateTaxNumber;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\CRM\CustomersResource;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateCustomer extends OrgAction
{
    use WithActionUpdate;

    private Customer $customer;

    public function handle(Customer $customer, array $modelData): Customer
    {
        if (Arr::has($modelData, 'contact_address')) {
            $contactAddressData = Arr::get($modelData, 'contact_address');
            Arr::forget($modelData, 'contact_address');

            $contactAddress=$customer->getAddress('contact');
            if($contactAddress) {
                UpdateAddress::run($contactAddress, $contactAddressData);
            } else {
                StoreAddressAttachToModel::run($customer, $contactAddressData, ['scope' => 'contact']);
            }

            $customer->location = $customer->getLocation();
            $customer->save();
        }
        if (Arr::has($modelData, 'delivery_address')) {
            $deliveryAddressData = Arr::get($modelData, 'delivery_address');
            Arr::forget($modelData, 'delivery_address');
            $deliveryAddress = $customer->getAddress('delivery');
            if ($deliveryAddressData) {
                if ($deliveryAddress) {
                    UpdateAddress::run($deliveryAddress, $deliveryAddressData);
                } else {
                    StoreAddressAttachToModel::run($customer, $deliveryAddressData, ['scope' => 'delivery']);
                }
            } elseif ($deliveryAddress) {
                $customer->addresses()->detach($deliveryAddress->id);
                $deliveryAddress->delete();
            }
        }

        if (Arr::has($modelData, 'tax_number')) {
            $taxNumberData = Arr::get($modelData, 'tax_number');
            Arr::forget($modelData, 'tax_number');

            if ($taxNumberData) {
                if (!$customer->taxNumber) {
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
                } else {
                    UpdateTaxNumber::run($customer->taxNumber, $taxNumberData);
                }
            } else {
                if ($customer->taxNumber) {
                    DeleteTaxNumber::run($customer->taxNumber);
                }
            }
        }


        if (Arr::hasAny($modelData, ['contact_name', 'company_name'])) {
            $contact_name = Arr::exists($modelData, 'contact_name') ? Arr::get($modelData, 'contact_name') : $customer->contact_name;
            $company_name = Arr::exists($modelData, 'company_name') ? Arr::get($modelData, 'company_name') : $customer->company_name;

            $modelData['name'] = $company_name ?: $contact_name;
        }

        $customer = $this->update($customer, $modelData, ['data']);


        CustomerHydrateUniversalSearch::dispatch($customer)->delay($this->hydratorsDelay);

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
            'contact_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'                    => [
                'sometimes',
                'nullable',
                'string',
                'max:255',

                new IUnique(
                    table: 'customers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                        ['column' => 'id', 'value' => $this->customer->id, 'operator' => '!=']
                    ]
                ),
            ],
            'phone'                    => ['sometimes', 'nullable', 'max:255'],
            'identity_document_number' => ['sometimes', 'nullable', 'string'],
            'contact_website'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_address'          => ['sometimes', 'required', new ValidAddress()],
            'delivery_address'         => ['sometimes', 'nullable', new ValidAddress()],
            'timezone_id'              => ['sometimes', 'nullable', 'exists:timezones,id'],
            'language_id'              => ['sometimes', 'nullable', 'exists:languages,id'],
        ];

        if ($this->strict) {
            $strictRules = [
                'phone'           => ['sometimes', 'nullable', 'phone:AUTO'],
                'contact_website' => ['sometimes', 'nullable', 'active_url'],
                'email'           => [
                    'sometimes',
                    'nullable',
                    'email',
                    new IUnique(
                        table: 'customers',
                        extraConditions: [
                            ['column' => 'shop_id', 'value' => $this->shop->id],
                            ['column' => 'deleted_at', 'operator'=>'notNull'],
                            ['column' => 'id', 'value' => $this->customer->id, 'operator' => '!=']
                        ]
                    ),
                ],
            ];
            $rules       = array_merge($rules, $strictRules);
        }

        return $rules;
    }


    public function asController(Organisation $organisation, Shop $shop, Customer $customer, ActionRequest $request): Customer
    {
        $this->customer = $customer;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($customer, $this->validatedData);
    }

    public function action(Customer $customer, array $modelData, int $hydratorsDelay = 0, bool $strict=true): Customer
    {
        $this->asAction       = true;
        $this->customer       = $customer;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $this->validatedData);
    }


    public function jsonResponse(Customer $customer): CustomersResource
    {
        return new CustomersResource($customer);
    }
}
