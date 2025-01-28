<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Jan 2025 16:38:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\InvoiceTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithOrderExchanges;
use App\Models\Accounting\InvoiceTransaction;
use Illuminate\Support\Facades\DB;

class StoreRefundInvoiceTransaction extends OrgAction
{
    use WithFixedAddressActions;
    use WithOrderExchanges;
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(InvoiceTransaction $invoiceTransaction, array $modelData): InvoiceTransaction
    {


        $invoice = $invoiceTransaction->invoice;

        data_set($modelData, 'group_id', $invoiceTransaction->group_id);
        data_set($modelData, 'organisation_id', $invoiceTransaction->organisation_id);
        data_set($modelData, 'shop_id', $invoiceTransaction->shop_id);
        data_set($modelData, 'customer_id', $invoiceTransaction->customer_id);

        data_set($modelData, 'net_amount', $invoiceTransaction->net_amount);
        data_set($modelData, 'date', now());
        data_set($modelData, 'gross_amount', $invoiceTransaction->gross_amount);
        data_set($modelData, 'grp_net_amount', $invoiceTransaction->grp_net_amount);
        data_set($modelData, 'org_net_amount', $invoiceTransaction->org_net_amount);
        data_set($modelData, 'quantity', $invoiceTransaction->quantity);
        data_set($modelData, 'profit_amount', $invoiceTransaction->profit_amount);
        data_set($modelData, 'model_type', $invoiceTransaction->model_type);
        data_set($modelData, 'invoice_id', $invoice->id);

        data_set($modelData, 'tax_category_id', $invoiceTransaction->tax_category_id);
        data_set($modelData, 'model_id', $invoiceTransaction->model_id);
        data_set($modelData, 'asset_id', $invoiceTransaction->asset_id);
        data_set($modelData, 'department_id', $invoiceTransaction->department_id);
        data_set($modelData, 'order_id', $invoiceTransaction->order_id);
        data_set($modelData, 'transaction_id', $invoiceTransaction->transaction_id);
        data_set($modelData, 'family_id', $invoiceTransaction->family_id);
        data_set($modelData, 'recurring_bill_transaction_id', $invoiceTransaction->recurring_bill_transaction_id);
        data_set($modelData, 'data', $invoiceTransaction->data);
        data_set($modelData, 'in_process', true);

        return DB::transaction(function () use ($invoice, $invoiceTransaction, $modelData) {
            $invoiceTransaction = $invoiceTransaction->transactionRefunds()->create($modelData);
            $newDataInvoice = [
                'total_amount' => $invoice->total_amount + $invoiceTransaction->net_amount,
                'tax_amount' => $invoice->tax_amount + $invoiceTransaction->tax_amount,
                'grp_net_amount' => $invoice->grp_net_amount + $invoiceTransaction->grp_net_amount,
                'org_net_amount' => $invoice->org_net_amount + $invoiceTransaction->org_net_amount,
            ];

            if ($invoiceTransaction->model_type == 'Rental') {
                $newDataInvoice['rental_amount'] = $invoice->rental_amount + $invoiceTransaction->net_amount;
            } elseif ($invoiceTransaction->model_type == 'Charge') {
                $newDataInvoice['charges_amount'] = $invoice->charges_amount + $invoiceTransaction->net_amount;
            } elseif ($invoiceTransaction->model_type == 'Service') {
                $newDataInvoice['services_amount'] = $invoice->services_amount + $invoiceTransaction->net_amount;
            } elseif ($invoiceTransaction->model_type == 'Product') {
                $newDataInvoice['goods_amount'] = $invoice->goods_amount + $invoiceTransaction->net_amount;
            } elseif ($invoiceTransaction->model_type == 'ShippingZone') {
                $newDataInvoice['shipping_amount'] = $invoice->shipping_amount + $invoiceTransaction->net_amount;
            }

            $invoice->update($newDataInvoice);

            return $invoiceTransaction;
        });
    }

    public function rules(): array
    {
        return[
            'amount' => ['required', 'numeric'],
        ];
    }


    /**
     * @throws \Throwable
     */
    public function asController(InvoiceTransaction $invoiceTransaction, $request): InvoiceTransaction
    {
        $this->initialisationFromShop($invoiceTransaction->shop, $request);
        return $this->handle($invoiceTransaction, $this->validatedData);
    }


}
