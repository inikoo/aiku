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
        $payStatus             = InvoicePayStatusEnum::UNPAID;
        $paymentAt             = null;
        $runningPaymentsAmount = 0;

        /** @var Payment $payment */
        foreach (
            $invoice->payments()->where('payments.status', PaymentStatusEnum::SUCCESS)->get() as $payment
        ) {
            $runningPaymentsAmount += $payment->amount;
            if ($payStatus == InvoicePayStatusEnum::UNPAID && $runningPaymentsAmount >= $invoice->total_amount) {
                $payStatus = InvoicePayStatusEnum::PAID;
                $paymentAt = $payment->date;
            }
        }

        if ($payStatus == InvoicePayStatusEnum::UNPAID && $invoice->created_at->diffInYears(now()) > 1) {
            $payStatus = InvoicePayStatusEnum::UNKNOWN;
        }


        $invoice->update(
            [
                'pay_status'     => $payStatus,
                'paid_at'        => $paymentAt,
                'payment_amount' => $runningPaymentsAmount
            ]
        );

        return $invoice;
    }

}
