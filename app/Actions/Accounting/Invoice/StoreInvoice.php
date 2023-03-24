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
use App\Models\Accounting\Invoice;
use App\Models\Helpers\Address;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreInvoice
{
    use AsAction;

    public function handle(
        Order $order,
        array $modelData,
        Address $billingAddress,
    ): Invoice {
        $modelData['currency_id'] = $order->shop->currency_id;
        $modelData['shop_id']     = $order->shop_id;
        $modelData['customer_id'] = $order->customer_id;


        /** @var \App\Models\Accounting\Invoice $invoice */
        $invoice        = $order->invoices()->create($modelData);
        $invoice->stats()->create();

        $billingAddress = StoreHistoricAddress::run($billingAddress);
        AttachHistoricAddressToModel::run($invoice, $billingAddress, ['scope' => 'billing']);

        InvoiceHydrateUniversalSearch::dispatch($invoice);


        return $invoice;
    }
}
