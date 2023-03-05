<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 19:39:45 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Invoice;

use App\Actions\Helpers\Address\AttachHistoricAddressToModel;
use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Models\Helpers\Address;
use App\Models\Sales\Invoice;
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


        /** @var Invoice $invoice */
        $invoice        = $order->invoices()->create($modelData);
        $invoice->stats()->create();

        $billingAddress = StoreHistoricAddress::run($billingAddress);
        AttachHistoricAddressToModel::run($invoice, $billingAddress, ['scope' => 'billing']);


        return $invoice;
    }
}
