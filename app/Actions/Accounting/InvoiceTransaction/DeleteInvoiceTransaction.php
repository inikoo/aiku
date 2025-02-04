<?php

/*
 * author Arya Permana - Kirin
 * created on 04-02-2025-09h-12m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\InvoiceTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Accounting\InvoiceTransaction;
use Lorisleiva\Actions\ActionRequest;

class DeleteInvoiceTransaction extends OrgAction
{
    use WithActionUpdate;

    public function handle(InvoiceTransaction $invoiceTransaction): void
    {
        $invoiceTransaction->delete();

    }

    //todo authorisation
    public function fromRetina(InvoiceTransaction $invoiceTransaction, ActionRequest $request): void
    {
        $this->initialisationFromShop($invoiceTransaction->shop, $request);

        $this->handle($invoiceTransaction);
    }

    public function asController(InvoiceTransaction $invoiceTransaction, ActionRequest $actionRequest): void
    {
        $this->initialisationFromShop($invoiceTransaction->shop, $actionRequest);

        $this->handle($invoiceTransaction);
    }


    public function action(InvoiceTransaction $invoiceTransaction): void
    {
        $this->initialisationFromShop($invoiceTransaction->shop, []);

        $this->handle($invoiceTransaction);
    }

}
