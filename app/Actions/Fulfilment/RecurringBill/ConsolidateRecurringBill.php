<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 16:53:00 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\Accounting\InvoiceTransaction\StoreInvoiceTransaction;
use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateRecurringBills;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateRecurringBills;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateRecurringBills;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRecurringBills;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Models\Accounting\Invoice;
use App\Models\Fulfilment\RecurringBill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class ConsolidateRecurringBill extends OrgAction
{
    use WithActionUpdate;


    /**
     * @throws \Throwable
     */
    public function handle(RecurringBill $recurringBill): Invoice
    {
        $recurringBill       = $this->update($recurringBill, [
            'status' => RecurringBillStatusEnum::FORMER
        ]);


        $invoiceData = [
            'reference'        => $recurringBill->reference,
            'currency_id'      => $recurringBill->currency_id,
            'type'             => InvoiceTypeEnum::INVOICE,
            'net_amount'       => $recurringBill->net_amount,
            'total_amount'     => $recurringBill->total_amount,
            'gross_amount'     => $recurringBill->gross_amount,
            'rental_amount'    => $recurringBill->rental_amount,
            'goods_amount'     => $recurringBill->goods_amount,
            'services_amount'  => $recurringBill->services_amount,
            'tax_amount'       => $recurringBill->tax_amount
        ];

        $invoice = StoreInvoice::make()->action($recurringBill, $invoiceData);

        $transactions = $recurringBill->transactions;

        foreach ($transactions as $transaction) {

            $data = [
                'tax_category_id' => $transaction->recurringBill->tax_category_id,
                'quantity'        => $transaction->quantity*$transaction->temporal_quantity,
                'gross_amount'    => $transaction->gross_amount,
                'net_amount'      => $transaction->net_amount,
            ];
            StoreInvoiceTransaction::make()->action($invoice, $transaction->historicAsset, $data);
        }
        $hasStoringPallet = $recurringBill->fulfilmentCustomer->pallets()
        ->where('status', PalletStatusEnum::STORING)
        ->exists();

        if ($hasStoringPallet) {
            $newRecurringBill = StoreRecurringBill::make()->action(
                rentalAgreement: $recurringBill->fulfilmentCustomer->rentalAgreement,
                modelData: ['start_date' => now()],
                previousRecurringBill: $recurringBill,
                strict: true
            );

            $this->update($recurringBill->fulfilmentCustomer, ['current_recurring_bill_id' => $newRecurringBill->id]);
        } else {
            $this->update($recurringBill->fulfilmentCustomer, ['current_recurring_bill_id' => null]);
        }

        GroupHydrateRecurringBills::dispatch($recurringBill->group);
        OrganisationHydrateRecurringBills::dispatch($recurringBill->organisation);
        FulfilmentHydrateRecurringBills::dispatch($recurringBill->fulfilment);
        FulfilmentCustomerHydrateRecurringBills::dispatch($recurringBill->fulfilmentCustomer);

        return $invoice;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function htmlResponse(Invoice $invoice, ActionRequest $request): RedirectResponse
    {
        return Redirect::route(
          'grp.org.fulfilments.show.crm.customers.show.invoices.show',
            [
                $invoice->organisation->slug,
                $invoice->shop->fulfilment->slug,
                $invoice->customer->fulfilmentCustomer->slug,
                $invoice->slug
            ]

        );
    }


    /**
     * @throws \Throwable
     */
    public function asController(RecurringBill $recurringBill, ActionRequest $request): Invoice
    {

        $this->initialisationFromFulfilment($recurringBill->fulfilment, $request);
        return $this->handle($recurringBill);
    }

    /**
     * @throws \Throwable
     */
    public function action(RecurringBill $recurringBill): Invoice
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($recurringBill->fulfilment, []);

        return $this->handle($recurringBill);
    }


}
