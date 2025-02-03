<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\Refund\UI;

use App\Actions\Accounting\Refund\DestroyRefund;
use App\Actions\OrgAction;
use App\Models\Accounting\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class DeleteRefund extends OrgAction
{
    public function handle(Invoice $invoice): Invoice
    {

        if (!$invoice->in_process) {
            return $invoice;
        }
        DestroyRefund::make()->action($invoice, []);
        return $invoice;
    }

    public function htmlResponse(Invoice $invoice, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilments.show.crm.customers.show.invoices.index', [
            $invoice->organisation->slug,
            $invoice->customer->fulfilmentCustomer->fulfilment->slug,
            $invoice->customer->fulfilmentCustomer->slug,
        ]);
    }

    public function asController(Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->initialisationFromShop($invoice->shop, $request);

        return $this->handle($invoice);
    }
}
