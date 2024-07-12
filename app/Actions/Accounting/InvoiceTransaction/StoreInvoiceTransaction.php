<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\InvoiceTransaction;

use App\Actions\OrgAction;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Catalogue\HistoricAsset;
use App\Models\Catalogue\Product;
use App\Models\Ordering\Transaction;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreInvoiceTransaction extends OrgAction
{
    use AsAction;

    public function handle(Invoice $invoice, Transaction|HistoricAsset $model, array $modelData): InvoiceTransaction
    {


        data_set($modelData, 'date', now(), overwrite: false);

        $modelData['shop_id']           = $invoice->shop_id;
        $modelData['customer_id']       = $invoice->customer_id;
        $modelData['group_id']          = $invoice->group_id;
        $modelData['organisation_id']   = $invoice->organisation_id;

        data_set($modelData, 'org_exchange', $invoice->org_exchange, overwrite: false);
        data_set($modelData, 'grp_exchange', $invoice->grp_exchange, overwrite: false);

        data_set($modelData, 'grp_net_amount', Arr::get($modelData, 'net_amount'), Arr::get($modelData, 'grp_exchange'));
        data_set($modelData, 'org_net_amount', Arr::get($modelData, 'net_amount'), Arr::get($modelData, 'org_exchange'));


        if($model instanceof Transaction) {
            $modelData['transaction_id'] = $model->id;
            $modelData['order_id']       = $model->order_id;
            if($this->strict) {
                $historicAsset               = $model->historicAsset;
            } else {
                $historicAsset               = $model->historicAsset()->withTrashed()->first();
            }

        } else {
            $historicAsset = $model;

        }

        $modelData['asset_id']          = $historicAsset->asset_id;
        $modelData['historic_asset_id'] = $historicAsset->id;

        if ($historicAsset->model_type == 'Product') {
            /** @var Product $product */
            $product = $historicAsset->model;

            $modelData['family_id']     = $product->family_id;
            $modelData['department_id'] = $product->department_id;
        }

        /** @var InvoiceTransaction $invoiceTransaction */
        $invoiceTransaction = $invoice->invoiceTransactions()->create($modelData);

        if($model instanceof Transaction) {
            $model->update([
                'invoice_id' => $invoice->id
            ]);
        }

        return $invoiceTransaction;
    }

    public function rules(): array
    {
        return [
            'date'                => ['sometimes', 'required', 'date'],
            'created_at'          => ['sometimes', 'required', 'date'],
            'tax_category_id'     => ['required', 'exists:tax_categories,id'],
            'quantity'            => ['required', 'numeric'],
            'net_amount'          => ['required', 'numeric'],
            'source_id'           => ['sometimes', 'required', 'string'],
            'org_exchange'        => ['sometimes', 'numeric'],
            'grp_exchange'        => ['sometimes', 'numeric'],

        ];
    }


    public function action(Invoice $invoice, Transaction|HistoricAsset $model, array $modelData, bool $strict=true): InvoiceTransaction
    {
        $this->asAction = true;
        $this->strict   = $strict;
        $this->initialisationFromShop($invoice->shop, $modelData);
        return $this->handle($invoice, $model, $this->validatedData);
    }


}
