<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\InvoiceTransaction;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreInvoiceTransaction
{
    use AsAction;

    public function handle(Invoice $invoice, array $modelData): InvoiceTransaction
    {
        $modelData['shop_id']     = $invoice->shop_id;
        $modelData['customer_id'] = $invoice->customer_id;
        $modelData['order_id']    = $invoice->order_id;
        /** @var InvoiceTransaction $invoiceTransaction */
        $invoiceTransaction = $invoice->invoiceTransactions()->create($modelData);

        return $invoiceTransaction;
    }
}
