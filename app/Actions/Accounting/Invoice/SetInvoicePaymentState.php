<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Feb 2025 19:54:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\OrgAction;
use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Enums\Accounting\Invoice\InvoicePayStatusEnum;
use App\Enums\Accounting\Payment\PaymentStatusEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;

class SetInvoicePaymentState extends OrgAction
{
    use WithHydrateCommand;

    public string $commandSignature = 'invoices:set_payment_state {organisations?*} {--S|shop= shop slug} {--s|slug=}';


    public function __construct()
    {
        $this->model = Invoice::class;
    }

    protected function handle(Invoice $invoice): Invoice
    {
        $runningPaymentsAmount = 0;
        $payStatus             = InvoicePayStatusEnum::UNPAID;
        $paymentAt             = null;

        /** @var Payment $payment */
        foreach (
            $invoice->payments()->where('payments.status', PaymentStatusEnum::SUCCESS)->get() as $payment
        ) {
            $runningPaymentsAmount += $payment->amount;
            if ($runningPaymentsAmount >= $invoice->total_amount) {
                $payStatus = InvoicePayStatusEnum::PAID;
                $paymentAt = $payment->date;
            }
        }


        $invoice->update(
            [
                'pay_status' => $payStatus,
                'paid_at'    => $paymentAt
            ]
        );

        return $invoice;
    }

}
