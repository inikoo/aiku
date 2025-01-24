<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Refund\UI;

use App\Actions\Accounting\Refund\StoreRefundInvoiceTransaction;
use App\Actions\OrgAction;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class CreateRefundInvoiceTransaction extends OrgAction
{
    public function handle(Invoice $invoice, InvoiceTransaction $invoiceTransaction): InvoiceTransaction
    {
        return StoreRefundInvoiceTransaction::make()->action($invoice, $invoiceTransaction, []);
    }

    // public function htmlResponse(Invoice $invoice, ActionRequest $request): RedirectResponse
    // {
    //     return Redirect::route('grp.org.fulfilments.show.crm.customers.show.refund.show', [
    //         $invoice->organisation->slug,
    //         $invoice->customer->fulfilmentCustomer->fulfilment->slug,
    //         $invoice->customer->fulfilmentCustomer->slug,
    //         $invoice->slug
    //     ]);
    // }

    public function asController(Invoice $invoice, InvoiceTransaction $invoiceTransaction, ActionRequest $request): void
    {
        $this->initialisationFromShop($invoice->shop, $request);

        $this->handle($invoice, $invoiceTransaction, $this->validatedData);
    }
}
