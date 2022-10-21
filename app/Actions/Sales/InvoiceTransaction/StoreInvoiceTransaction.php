<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 20:01:47 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\InvoiceTransaction;

use App\Models\Sales\Invoice;
use App\Models\Sales\InvoiceTransaction;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreInvoiceTransaction
{
    use AsAction;

    public function handle(Invoice $invoice, array $modelData): InvoiceTransaction
    {
        $modelData['shop_id']         = $invoice->shop_id;
        $modelData['customer_id']     = $invoice->customer_id;
        /** @var InvoiceTransaction $invoiceTransaction */
        $invoiceTransaction= $invoice->invoiceTransactions()->create($modelData);
        return $invoiceTransaction;
    }
}
