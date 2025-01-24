<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\Refund;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithOrderExchanges;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;

class StoreRefundInvoiceTransaction extends OrgAction
{
    use WithFixedAddressActions;
    use WithOrderExchanges;
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Invoice $invoice, InvoiceTransaction $invoiceTransaction, array $modelData): InvoiceTransaction
    {
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

        return $invoiceTransaction->transactionRefunds()->create($modelData);
    }

    // public function rules(): array
    // {
    //     return[
    //         'invoice_id' => 'required|exists:invoices,id',
    //     ];
    // }

    public function action(Invoice $invoice, InvoiceTransaction $invoiceTransaction, array $modelData): InvoiceTransaction
    {
        $this->initialisationFromShop($invoice->shop, $modelData);

        return $this->handle($invoice, $invoiceTransaction, $this->validatedData);
    }
}
