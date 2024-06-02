<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\InvoiceTransaction;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Catalogue\HistoricAsset;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreInvoiceTransaction
{
    use AsAction;

    public function handle(Invoice $invoice, HistoricAsset $historicOuterable, array $modelData): InvoiceTransaction
    {
        $modelData['shop_id']            = $invoice->shop_id;
        $modelData['customer_id']        = $invoice->customer_id;
        $modelData['group_id']           = $invoice->group_id;
        $modelData['organisation_id']    = $invoice->organisation_id;



        $modelData['product_id']    = $historicOuterable->product_id;
        $modelData['item_type']     = class_basename($historicOuterable);
        $modelData['item_id']       = $historicOuterable->id;

        $modelData['family_id']           = $historicOuterable->product->family_id;
        $modelData['department_id']       = $historicOuterable->product->department_id;


        /** @var InvoiceTransaction $invoiceTransaction */
        $invoiceTransaction = $invoice->invoiceTransactions()->create($modelData);

        return $invoiceTransaction;
    }
}
