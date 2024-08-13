<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\Invoice\Search\InvoiceRecordSearch;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateInvoices;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateSales;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateInvoices;
use App\Actions\Helpers\TaxCategory\GetTaxCategory;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateInvoices;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSales;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateInvoices;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateSales;
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
use Illuminate\Validation\Rule;

class StoreInvoice extends OrgAction
{
    use WithFixedAddressActions;
    use WithOrderExchanges;

    public function handle(
        Customer|Order|RecurringBill $parent,
        array $modelData,
    ): Invoice {
        if (class_basename($parent) == 'Customer') {
            $modelData['customer_id'] = $parent->id;
        } else {
            $modelData['customer_id'] = $parent->customer_id;
        }

        if (!Arr::exists($modelData, 'tax_category_id')) {
            if ($parent instanceof Order || $parent instanceof RecurringBill) {
                $modelData['tax_category_id'] = $parent->tax_category_id;
            } else {
                $customer = Customer::find($modelData['customer_id']);
                data_set(
                    $modelData,
                    'tax_category_id',
                    GetTaxCategory::run(
                        country: $this->organisation->country,
                        taxNumber: $customer->taxNumber,
                        billingAddress: $modelData['billing_address'],
                        deliveryAddress: $modelData['billing_address']
                    )->id
                );
            }
        }


        $billingAddressData = $modelData['billing_address'];
        data_forget($modelData, 'billing_address');


        $modelData['shop_id']     = $this->shop->id;
        $modelData['currency_id'] = $this->shop->currency_id;

        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);

        $modelData = $this->processExchanges($modelData, $this->shop);


        $date = now();
        data_set($modelData, 'date', $date, overwrite: false);
        data_set($modelData, 'tax_liability_at', $date, overwrite: false);

        // dd($modelData);
        /** @var Invoice $invoice */
        $invoice = $parent->invoices()->create($modelData);
        $invoice->stats()->create();


        $invoice = $this->createFixedAddress(
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

        if ($invoice->customer_id) {
            CustomerHydrateInvoices::dispatch($invoice->customer)->delay($this->hydratorsDelay);
        }

        // UploadPDFInvoices
        // UploadPdfInvoice::run($invoice);

        ShopHydrateInvoices::dispatch($invoice->shop)->delay($this->hydratorsDelay);
        OrganisationHydrateInvoices::dispatch($invoice->organisation)->delay($this->hydratorsDelay);
        GroupHydrateInvoices::dispatch($invoice->group)->delay($this->hydratorsDelay);

        ShopHydrateSales::dispatch($invoice->shop)->delay($this->hydratorsDelay);
        OrganisationHydrateSales::dispatch($invoice->organisation)->delay($this->hydratorsDelay);
        GroupHydrateSales::dispatch($invoice->group)->delay($this->hydratorsDelay);

        InvoiceRecordSearch::dispatch($invoice);


        return $invoice;
    }


    public function rules(): array
    {
        $rules = [
            'reference'           => [
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
            'currency_id'      => ['required', 'exists:currencies,id'],
            'billing_address'  => ['required', new ValidAddress()],
            'type'             => ['required', Rule::enum(InvoiceTypeEnum::class)],
            'net_amount'       => ['required', 'numeric'],
            'total_amount'     => ['required', 'numeric'],
            'date'             => ['sometimes', 'date'],
            'tax_liability_at' => ['sometimes', 'date'],
            'created_at'       => ['sometimes', 'date'],
            'data'             => ['sometimes', 'array'],
            'source_id'        => ['sometimes', 'string'],
            'tax_category_id'  => ['sometimes', 'required', 'exists:tax_categories,id'],
        ];

        if (!$this->strict) {
            $rules['reference'] = ['required', 'max:64', 'string'];


            $rules['grp_exchange'] = ['sometimes', 'numeric'];
            $rules['org_exchange'] = ['sometimes', 'numeric'];

            $rules['gross_amount']    = ['sometimes', 'numeric'];
            $rules['goods_amount']    = ['sometimes', 'numeric'];
            $rules['services_amount'] = ['sometimes', 'numeric'];

            $rules['shipping_amount']  = ['sometimes', 'numeric'];
            $rules['charges_amount']   = ['sometimes', 'numeric'];
            $rules['insurance_amount'] = ['sometimes', 'numeric'];

            $rules['net_amount']   = ['sometimes', 'numeric'];
            $rules['tax_amount']   = ['sometimes', 'numeric'];
            $rules['total_amount'] = ['sometimes', 'numeric'];
        }


        return $rules;
    }

    public function action(Customer|Order|RecurringBill $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Invoice
    {
        // dd('bb');
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;




        if($parent instanceof RecurringBill){
            $this->shop = $parent->fulfilment->shop;
            $this->initialisationFromFulfilment($parent->fulfilment, $modelData);
        } else {
          
            $this->initialisationFromShop($parent->shop, $modelData);
        }

        return $this->handle($parent, $this->validatedData);
    }
}
