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
use App\Models\Catalogue\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreInvoiceTransaction
{
    use AsAction;

    public function handle(Invoice $invoice, HistoricAsset $historicAsset, array $modelData): InvoiceTransaction
    {
        $modelData['shop_id']           = $invoice->shop_id;
        $modelData['customer_id']       = $invoice->customer_id;
        $modelData['group_id']          = $invoice->group_id;
        $modelData['organisation_id']   = $invoice->organisation_id;
        $modelData['asset_id']          = $historicAsset->asset_id;
        $modelData['model_type']        = $historicAsset->model_type;
        $modelData['model_id']          = $historicAsset->model_id;
        $modelData['historic_asset_id'] = $historicAsset->id;

        if ($historicAsset->model_type == 'Product') {
            /** @var Product $product */
            $product = $historicAsset->model;

            $modelData['family_id']     = $product->family_id;
            $modelData['department_id'] = $product->department_id;
        }


        /** @var InvoiceTransaction $invoiceTransaction */
        $invoiceTransaction = $invoice->invoiceTransactions()->create($modelData);

        return $invoiceTransaction;
    }
}
