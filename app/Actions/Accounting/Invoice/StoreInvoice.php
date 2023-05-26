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
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreInvoice
{
    use AsAction;
    public int $hydratorsDelay=0;

    public function handle(
        Order $order,
        array $modelData,
        Address $billingAddress,
    ): Invoice {
        $modelData['currency_id'] = $order->shop->currency_id;
        $modelData['shop_id']     = $order->shop_id;
        $modelData['customer_id'] = $order->customer_id;


        /** @var \App\Models\Accounting\Invoice $invoice */
        $invoice = $order->invoices()->create($modelData);
        $invoice->stats()->create();

        $billingAddress = StoreHistoricAddress::run($billingAddress);
        AttachHistoricAddressToModel::run($invoice, $billingAddress, ['scope' => 'billing']);

        CustomerHydrateInvoices::dispatch($invoice->customer)->delay($this->hydratorsDelay);
        ShopHydrateInvoices::dispatch($invoice->shop)->delay($this->hydratorsDelay);
        InvoiceHydrateUniversalSearch::dispatch($invoice);


        return $invoice;
    }

    public function asFetch(
        Order $order,
        array $modelData,
        Address $billingAddress,
        int $hydratorsDelay = 60
    ): Invoice {
        $this->hydratorsDelay = $hydratorsDelay;

        return $this->handle($order, $modelData, $billingAddress);
    }
}
