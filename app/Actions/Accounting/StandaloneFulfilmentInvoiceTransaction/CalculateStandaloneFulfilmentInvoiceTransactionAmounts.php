<?php

/*
 * author Arya Permana - Kirin
 * created on 27-02-2025-12h-15m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\StandaloneFulfilmentInvoiceTransaction;

use App\Enums\Fulfilment\RentalAgreementClause\RentalAgreementCauseStateEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use Lorisleiva\Actions\Concerns\AsObject;

class CalculateStandaloneFulfilmentInvoiceTransactionAmounts
{
    use AsObject;
    public function handle(InvoiceTransaction $invoiceTransaction): InvoiceTransaction
    {
        $grossAmount = $invoiceTransaction->historicAsset->price * $invoiceTransaction->quantity;

        $netAmount = $this->getDiscounts($invoiceTransaction->invoice, $grossAmount, $invoiceTransaction->historicAsset->asset_id);
        $invoiceTransaction->update([
            'gross_amount' => $grossAmount,
            'net_amount'   => $netAmount ,
        ]);

        return $invoiceTransaction;
    }

    public function getDiscounts(Invoice $invoice, $grossAmount, int $assetID)
    {

        $rentalAgreementClause = $invoice->customer->fulfilmentCustomer->rentalAgreementClauses()
            ->where('state', RentalAgreementCauseStateEnum::ACTIVE)
            ->where('asset_id', $assetID)
            ->first();

        $percentageOff = 0;
        if ($rentalAgreementClause) {

            $percentageOff = $rentalAgreementClause->percentage_off / 100;

        }

        return $grossAmount - ($grossAmount * $percentageOff);
    }


}
