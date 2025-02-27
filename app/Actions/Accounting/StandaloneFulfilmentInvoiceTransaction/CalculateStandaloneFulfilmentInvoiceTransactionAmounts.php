<?php

/*
 * author Arya Permana - Kirin
 * created on 27-02-2025-12h-15m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\StandaloneFulfilmentInvoiceTransaction;

use App\Models\Accounting\InvoiceTransaction;
use Lorisleiva\Actions\Concerns\AsObject;

class CalculateStandaloneFulfilmentInvoiceTransactionAmounts
{
    use AsObject;
    public function handle(InvoiceTransaction $invoiceTransaction): InvoiceTransaction
    {
        $grossAmount = $invoiceTransaction->historicAsset->price * $invoiceTransaction->quantity;

        $invoiceTransaction->update([
            'gross_amount' => $grossAmount,
            'net_amount'   => $grossAmount ,
        ]);

        return $invoiceTransaction;
    }


}
