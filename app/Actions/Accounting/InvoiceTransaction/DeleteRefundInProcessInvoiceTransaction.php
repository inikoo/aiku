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
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\InvoiceTransaction;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class DeleteRefundInProcessInvoiceTransaction extends OrgAction
{
    use WithActionUpdate;

    public function handle(InvoiceTransaction $invoiceTransaction): void
    {
        $invoiceTransaction->delete();
    }


    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        $invoiceTransaction = $request->route()->parameter('invoiceTransaction');

        if ($invoiceTransaction->invoice->type != InvoiceTypeEnum::REFUND) {
            $validator->errors()->add('invoiceTransaction', 'Transaction is not a refund');
        }

        if (!$invoiceTransaction->invoice->in_process) {
            $validator->errors()->add('invoiceTransaction', 'Refund is not in process');
        }
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
