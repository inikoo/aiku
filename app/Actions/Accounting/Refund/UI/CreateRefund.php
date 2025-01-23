<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Refund\UI;

use App\Actions\Accounting\Refund\StoreRefund;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Ordering\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class CreateRefund extends OrgAction
{
    use WithFixedAddressActions;
    use WithOrderExchanges;
    use WithNoStrictRules;


    private Order|Customer|RecurringBill $parent;

    public function handle(Invoice $invoice): Invoice
    {
        $modelData = $invoice->toArray();
        $parent = $invoice->customer;
        $reference = $invoice->reference . '-refund-' . rand(000, 999);

        data_set($modelData, 'reference', $reference);
        data_set($modelData, 'type', InvoiceTypeEnum::REFUND);
        data_set($modelData, 'total_amount', 0);
        data_set($modelData, 'gross_amount', 0);
        data_set($modelData, 'goods_amount', 0);
        data_set($modelData, 'net_amount', 0);
        data_set($modelData, 'grp_net_amount', 0);
        data_set($modelData, 'org_net_amount', 0);
        data_set($modelData, 'tax_amount', 0);
        data_set($modelData, 'in_process', true);
        data_set($modelData, 'invoice_id', $invoice->id);

        $date = now();
        data_set($modelData, 'date', $date, overwrite: false);

        return StoreRefund::make()->action($parent, $modelData);
    }

    public function htmlResponse(Invoice $invoice, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilments.show.crm.customers.show.refund.show', [
            $invoice->organisation->slug,
            $invoice->customer->fulfilmentCustomer->fulfilment->slug,
            $invoice->customer->fulfilmentCustomer->slug,
            $invoice->slug
        ]);
    }

    public function asController(Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->initialisationFromShop($invoice->shop, $request);

        return $this->handle($invoice);
    }
}
