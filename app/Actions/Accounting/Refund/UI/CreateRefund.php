<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Refund\UI;

use App\Actions\Accounting\Refund\StoreRefund;
use App\Actions\OrgAction;
use App\Models\Accounting\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class CreateRefund extends OrgAction
{
    /**
     * @var \App\Models\Accounting\Invoice
     */
    private Invoice $invoice;

    public function handle(Invoice $invoice): Invoice
    {
        return StoreRefund::make()->action($invoice, []);
    }

    public function htmlResponse(Invoice $refund, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilments.show.crm.customers.show.invoices.refund.show', [
            $refund->organisation->slug,
            $refund->customer->fulfilmentCustomer->fulfilment->slug,
            $refund->customer->fulfilmentCustomer->slug,
            $this->invoice->slug,
            $refund->slug
        ]);
    }

    public function asController(Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->invoice = $invoice;
        $this->initialisationFromShop($invoice->shop, $request);

        return $this->handle($invoice);
    }
}
