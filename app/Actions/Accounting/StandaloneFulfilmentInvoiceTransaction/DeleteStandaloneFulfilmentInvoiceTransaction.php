<?php

/*
 * author Arya Permana - Kirin
 * created on 27-02-2025-09h-06m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\StandaloneFulfilmentInvoiceTransaction;

use App\Actions\Accounting\InvoiceTransaction\DeleteInvoiceTransaction;
use App\Actions\Accounting\StandaloneFulfilmentInvoice\CalculateStandaloneFulfilmentInvoiceTotals;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithOrderExchanges;
use App\Models\Accounting\InvoiceTransaction;
use Lorisleiva\Actions\ActionRequest;

class DeleteStandaloneFulfilmentInvoiceTransaction extends OrgAction
{
    use WithOrderExchanges;
    use WithNoStrictRules;

    public function handle(InvoiceTransaction $invoiceTransaction): void
    {
        $invoice = $invoiceTransaction->invoice;
        DeleteInvoiceTransaction::make()->action($invoiceTransaction);

        $invoice->refresh();

        CalculateStandaloneFulfilmentInvoiceTotals::run($invoice);
        $invoiceTransaction;
    }

    public function rules(): array
    {
        $rules = [
            'quantity'            => ['sometimes', 'numeric', 'min:0'],
            'net_amount'          => ['sometimes', 'numeric'],
        ];
        return $rules;
    }


    public function asController(InvoiceTransaction $invoiceTransaction, ActionRequest $request): void
    {
        $this->initialisationFromShop($invoiceTransaction->shop, $request);

        $this->handle($invoiceTransaction, $this->validatedData);
    }


}
