<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\Invoice\Hydrators\InvoiceHydrateUniversalSearch;
use App\Actions\Helpers\Address\AttachHistoricAddressToModel;
use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateInvoices;
use App\Actions\Sales\Customer\Hydrators\CustomerHydrateInvoices;
use App\Models\Accounting\Invoice;
use App\Models\Helpers\Address;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreInvoice
{
    use AsAction;
    public int $hydratorsDelay=0;

    public function handle(
        Customer|Order $parent,
        array $modelData,
        Address $billingAddress,
    ): Invoice {


        if(class_basename($parent)=='Customer') {
            $modelData['customer_id'] = $parent->id;
        } else {
            $modelData['customer_id'] = $parent->customer_id;

        }
        $modelData['shop_id']     = $parent->shop_id;
        $modelData['currency_id'] = $parent->shop->currency_id;




        /** @var \App\Models\Accounting\Invoice $invoice */
        $invoice = $parent->invoices()->create($modelData);
        $invoice->stats()->create();

        $billingAddress = StoreHistoricAddress::run($billingAddress);
        AttachHistoricAddressToModel::run($invoice, $billingAddress, ['scope' => 'billing']);

        CustomerHydrateInvoices::dispatch($invoice->customer)->delay($this->hydratorsDelay);
        ShopHydrateInvoices::dispatch($invoice->shop)->delay($this->hydratorsDelay);
        InvoiceHydrateUniversalSearch::dispatch($invoice);


        return $invoice;
    }

    public function asFetch(
        Customer|Order $parent,
        array $modelData,
        Address $billingAddress,
        int $hydratorsDelay = 60
    ): Invoice {
        $this->hydratorsDelay = $hydratorsDelay;

        return $this->handle($parent, $modelData, $billingAddress);
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'unique:tenant.invoices', 'numeric'],
            'currency_id' => ['required', 'required', 'exists:central.currencies,id']
        ];
    }

    public function action(Customer|Order $parent, array $modelData, Address $billingAddress): Invoice
    {
        return $this->handle($parent, $modelData, $billingAddress);
    }
}
