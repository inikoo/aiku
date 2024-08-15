<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\CreditTransaction\StoreCreditTransaction;
use App\Actions\OrgAction;
use App\Enums\Accounting\Invoice\CreditTransactionTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;

class AttachPaymentToInvoice extends OrgAction
{
    public function handle(Invoice $invoice, Payment $payment, array $modelData): void
    {
        $paymentAmount = $invoice->payment_amount + $modelData['amount'];
        $invoice->payments()->attach($payment, [
            'amount' => $paymentAmount,
        ]);

        if($paymentAmount > $invoice->total_amount || $paymentAmount == $invoice->total_amount) {
            UpdateInvoice::make()->action($invoice, [
                'payment_amount' => $invoice->total_amount
            ]);
        } else {
            UpdateInvoice::make()->action($invoice, [
                'payment_amount' => $paymentAmount
            ]);
        };

        data_forget($modelData, 'reference');
        if ($paymentAmount > $invoice->total_amount) {
            $excessAmount = $paymentAmount - $invoice->total_amount;
            data_set($modelData, 'amount', $excessAmount);
            data_set($modelData, 'type', CreditTransactionTypeEnum::TRANSFER_IN);
            data_set($modelData, 'payment_id', $payment->id);
            StoreCreditTransaction::run($invoice->customer, $modelData);
        }
    }

    public function rules()
    {
        return [
            'amount'    => ['required', 'numeric'],
            'reference' => ['sometimes'],
        ];
    }

    public function action(Invoice $invoice, Payment $payment, array $modelData): void
    {
        $this->asAction = true;
        $this->initialisationFromShop($invoice->shop, $modelData);
        $this->handle($invoice, $payment, $modelData);
    }
}
