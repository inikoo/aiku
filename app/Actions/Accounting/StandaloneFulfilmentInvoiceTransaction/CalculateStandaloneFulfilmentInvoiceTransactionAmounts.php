<?php
/*
 * author Arya Permana - Kirin
 * created on 27-02-2025-12h-15m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\StandaloneFulfilmentInvoiceTransaction;

use App\Actions\OrgAction;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Fulfilment\RecurringBillTransaction;

class CalculateStandaloneFulfilmentInvoiceTransactionAmounts extends OrgAction
{
    public function handle(InvoiceTransaction $invoiceTransaction): InvoiceTransaction
    {
        $grossAmount = $invoiceTransaction->historicAsset->price * $invoiceTransaction->quantity;

        $invoiceTransaction->update([
            'gross_amount' => $grossAmount,
            'net_amount'   => $grossAmount ,
        ]);

        return $invoiceTransaction;
    }

    public function action(InvoiceTransaction $invoiceTransaction, int $hydratorsDelay = 0): InvoiceTransaction
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($invoiceTransaction->shop, []);

        return $this->handle($invoiceTransaction);
    }

}
