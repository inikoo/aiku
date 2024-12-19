<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\Invoice\Search\InvoiceRecordSearch;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateInvoicedCustomers;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateInvoices;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateInvoices;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateSales;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateInvoices;
use App\Actions\Helpers\TaxCategory\GetTaxCategory;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateInvoices;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSales;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateInvoices;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateSales;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Ordering\Order;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreInvoice extends OrgAction
{
    use WithFixedAddressActions;
    use WithOrderExchanges;
    use WithNoStrictRules;


    private Order|Customer|RecurringBill $parent;

    /**
     * @throws \Throwable
     */
    public function handle(Customer|Order|RecurringBill $parent, array $modelData): Invoice
    {
        if (class_basename($parent) == 'Customer') {
            $modelData['customer_id'] = $parent->id;
        } elseif (class_basename($parent) == 'RecurringBill') {
            $modelData['customer_id'] = $parent->fulfilmentCustomer->customer_id;
        } else {
            $modelData['customer_id'] = $parent->customer_id;
        }

        if (!Arr::has($modelData, 'billing_address')) {
            if ($parent instanceof Order) {
                $modelData['billing_address'] = $parent->billingAddress;
            } elseif ($parent instanceof RecurringBill) {
                $modelData['billing_address'] = $parent->fulfilmentCustomer->customer->address;
            } else {
                $modelData['billing_address'] = $parent->address;
            }
        }

        if (!Arr::exists($modelData, 'tax_category_id')) {
            if ($parent instanceof Order || $parent instanceof RecurringBill) {
                $modelData['tax_category_id'] = $parent->tax_category_id;
            } else {
                /** @var Customer $customer */
                $customer = Customer::find($modelData['customer_id']);

                $billingAddress  = $customer->address;
                $deliveryAddress = $customer->deliveryAddress;

                data_set(
                    $modelData,
                    'tax_category_id',
                    GetTaxCategory::run(
                        country: $this->organisation->country,
                        taxNumber: $customer->taxNumber,
                        billingAddress: $billingAddress,
                        deliveryAddress: $deliveryAddress
                    )->id
                );
            }
        }


        $billingAddressData = Arr::pull($modelData, 'billing_address');

        $modelData['shop_id']     = $this->shop->id;
        $modelData['currency_id'] = $this->shop->currency_id;

        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);

        $modelData = $this->processExchanges($modelData, $this->shop);


        $date = now();
        data_set($modelData, 'date', $date, overwrite: false);
        data_set($modelData, 'tax_liability_at', $date, overwrite: false);


        $invoice = DB::transaction(function () use ($parent, $modelData, $billingAddressData) {
            /** @var Invoice $invoice */
            $invoice = $parent->invoices()->create($modelData);
            $invoice->stats()->create();
            $this->createFixedAddress(
                $invoice,
                $billingAddressData,
                'Ordering',
                'billing',
                'address_id'
            );
            $invoice->updateQuietly(
                [
                    'billing_country_id' => $invoice->address->country_id
                ]
            );

            return $invoice;
        });


        if ($invoice->customer_id) {
            CustomerHydrateInvoices::dispatch($invoice->customer)->delay($this->hydratorsDelay);
        }

        // todo: Upload Invoices to Google Drive #544
        //UploadPdfInvoice::run($invoice);
                    /** @var Invoice $invoice */
        ShopHydrateInvoices::dispatch($invoice->shop)->delay($this->hydratorsDelay);
        OrganisationHydrateInvoices::dispatch($invoice->organisation)->delay($this->hydratorsDelay);
        GroupHydrateInvoices::dispatch($invoice->group)->delay($this->hydratorsDelay);

        ShopHydrateSales::dispatch($invoice->shop)->delay($this->hydratorsDelay);
        OrganisationHydrateSales::dispatch($invoice->organisation)->delay($this->hydratorsDelay);
        GroupHydrateSales::dispatch($invoice->group)->delay($this->hydratorsDelay);

        InvoiceRecordSearch::dispatch($invoice);
        
        foreach($invoice->invoiceTransactions as $transaction)
        {
            AssetHydrateInvoices::dispatch($transaction->asset)->delay($this->hydratorsDelay);
            AssetHydrateInvoicedCustomers::dispatch($transaction->asset)->delay($this->hydratorsDelay);
        }

        return $invoice;
    }


    public function rules(): array
    {
        $rules = [
            'reference'       => [
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
            'currency_id'     => ['required', 'exists:currencies,id'],
            'type'            => ['required', Rule::enum(InvoiceTypeEnum::class)],
            'net_amount'      => ['required', 'numeric'],
            'total_amount'    => ['required', 'numeric'],
            'gross_amount'    => ['required', 'numeric'],
            'rental_amount'   => ['sometimes', 'required', 'numeric'],
            'goods_amount'    => ['sometimes', 'required', 'numeric'],
            'services_amount' => ['sometimes', 'required', 'numeric'],
            'tax_amount'      => ['required', 'numeric'],


            'date'             => ['sometimes', 'date'],
            'tax_liability_at' => ['sometimes', 'date'],
            'data'             => ['sometimes', 'array'],
            'sales_channel_id' => [
                'sometimes',
                'required',
                Rule::exists('sales_channels', 'id')->where(function ($query) {
                    $query->where('group_id', $this->shop->group_id);
                })
            ],
        ];

        if (!$this->strict) {
            $rules['reference'] = [
                'required',
                'max:64',
                'string'
            ];

            $rules['tax_category_id'] = ['sometimes', 'required', 'exists:tax_categories,id'];
            $rules['billing_address'] = ['required', new ValidAddress()];
            $rules                    = $this->orderingAmountNoStrictFields($rules);
            $rules                    = $this->noStrictStoreRules($rules);
        }


        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(Customer|Order|RecurringBill $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Invoice
    {
        if (!$audit) {
            Invoice::disableAuditing();
        }
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $parent;


        if ($parent instanceof RecurringBill) {
            $this->shop = $parent->fulfilment->shop;
            $this->initialisationFromFulfilment($parent->fulfilment, $modelData);
        } else {
            $this->initialisationFromShop($parent->shop, $modelData);
        }

        return $this->handle($parent, $this->validatedData);
    }

}
