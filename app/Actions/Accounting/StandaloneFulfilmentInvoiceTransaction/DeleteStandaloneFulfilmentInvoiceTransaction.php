<?php

/*
 * author Arya Permana - Kirin
 * created on 27-02-2025-09h-06m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\StandaloneFulfilmentInvoiceTransaction;

use App\Actions\Accounting\InvoiceTransaction\DeleteRefundInProcessInvoiceTransaction;
use App\Actions\Accounting\StandaloneFulfilmentInvoice\CalculateStandaloneFulfilmentInvoiceTotals;
use App\Actions\OrgAction;
use App\Models\Accounting\InvoiceTransaction;
use Lorisleiva\Actions\ActionRequest;

class DeleteStandaloneFulfilmentInvoiceTransaction extends OrgAction
{
    public function handle(InvoiceTransaction $invoiceTransaction): void
    {
        $invoice = $invoiceTransaction->invoice;


        DeleteRefundInProcessInvoiceTransaction::make()->action($invoiceTransaction);
        $invoice->refresh();
        CalculateStandaloneFulfilmentInvoiceTotals::run($invoice);

    }


    public function asController(InvoiceTransaction $invoiceTransaction, ActionRequest $request): void
    {
        $this->initialisationFromShop($invoiceTransaction->shop, $request);

        $this->handle($invoiceTransaction);
    }


}
