<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\Invoice\Hydrators\InvoiceHydrateUniversalSearch;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateInvoices;
use App\Actions\Helpers\Address\AttachHistoricAddressToModel;
use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\Market\Shop\Hydrators\ShopHydrateInvoices;
use App\Actions\Market\Shop\Hydrators\ShopHydrateSales;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateInvoices;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSales;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateInvoices;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateSales;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\CRM\Customer;
use App\Models\OMS\Order;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreInvoice extends OrgAction
{
    private bool $strict = true;

    public function handle(
        Customer|Order $parent,
        array $modelData,
    ): Invoice {
        $billingAddress = $modelData['billing_address'];
        unset($modelData['billing_address']);


        if (class_basename($parent) == 'Customer') {
            $modelData['customer_id'] = $parent->id;
        } else {
            $modelData['customer_id'] = $parent->customer_id;
        }
        $modelData['shop_id']     = $parent->shop_id;
        $modelData['currency_id'] = $parent->shop->currency_id;

        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);


        $orgExchange   = GetCurrencyExchange::run($parent->shop->currency, $parent->organisation->currency);
        $groupExchange = GetCurrencyExchange::run($parent->shop->currency, $parent->organisation->group->currency);

        data_set($modelData, 'org_exchange', $orgExchange, overwrite: false);
        data_set($modelData, 'group_exchange', $groupExchange, overwrite: false);
        data_set($modelData, 'org_net_amount', Arr::get($modelData, 'net') * $orgExchange, overwrite: false);
        data_set($modelData, 'group_net_amount', Arr::get($modelData, 'net') * $groupExchange, overwrite: false);

        $date=now();
        data_set($modelData, 'date', $date, overwrite: false);
        data_set($modelData, 'tax_liability_at', $date, overwrite: false);


        /** @var Invoice $invoice */
        $invoice = $parent->invoices()->create($modelData);
        $invoice->stats()->create();

        $billingAddress = StoreHistoricAddress::run($billingAddress);
        AttachHistoricAddressToModel::run($invoice, $billingAddress, ['scope' => 'billing']);

        CustomerHydrateInvoices::dispatch($invoice->customer)->delay($this->hydratorsDelay);
        ShopHydrateInvoices::dispatch($invoice->shop)->delay($this->hydratorsDelay);
        OrganisationHydrateInvoices::dispatch($invoice->organisation)->delay($this->hydratorsDelay);
        GroupHydrateInvoices::dispatch($invoice->group)->delay($this->hydratorsDelay);

        ShopHydrateSales::dispatch($invoice->shop)->delay($this->hydratorsDelay);
        OrganisationHydrateSales::dispatch($invoice->organisation)->delay($this->hydratorsDelay);
        GroupHydrateSales::dispatch($invoice->group)->delay($this->hydratorsDelay);

        InvoiceHydrateUniversalSearch::dispatch($invoice);


        return $invoice;
    }


    public function rules(): array
    {
        $rules = [
            'number'           => [
                'required',
                'max:64',
                'string',
                new IUnique(
                    table: 'invoices',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                    ]
                ),
            ],
            'currency_id'             => ['required', 'exists:currencies,id'],
            'billing_address'         => ['required', new ValidAddress()],
            'type'                    => ['required', Rule::enum(InvoiceTypeEnum::class)],
            'net_amount'              => ['required', 'numeric'],
            'total_amount'            => ['required', 'numeric'],
            'source_id'               => ['sometimes', 'string'],
            'date'                    => ['sometimes', 'date'],
            'tax_liability_at'        => ['sometimes', 'date'],
            'created_at'              => ['sometimes', 'date'],
            'data'                    => ['sometimes', 'array'],
            'org_exchange'            => ['sometimes', 'numeric'],
            'group_exchange'          => ['sometimes', 'numeric'],
            'org_net_amount'          => ['sometimes', 'numeric'],
            'group_net_amount'        => ['sometimes', 'numeric'],
        ];

        if (!$this->strict) {
            $rules['number'] = ['required', 'max:64', 'string'];
        }


        return $rules;
    }

    public function action(Customer|Order $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Invoice
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($parent->shop, $modelData);


        return $this->handle($parent, $this->validatedData);
    }
}
