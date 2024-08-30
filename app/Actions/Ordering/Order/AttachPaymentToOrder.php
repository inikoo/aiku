<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Accounting\CreditTransaction\StoreCreditTransaction;
use App\Actions\OrgAction;
use App\Enums\Accounting\Invoice\CreditTransactionTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\Ordering\Order;

class AttachPaymentToOrder extends OrgAction
{
    public function handle(Order $order, Payment $payment, array $modelData): void
    {
        $paymentAmount = $order->payment_amount + $modelData['amount'];
        $order->payments()->attach($payment, [
            'amount' => $paymentAmount,
        ]);

        if($paymentAmount > $order->total_amount || $paymentAmount == $order->total_amount) {
            UpdateOrder::make()->action($order, [
                'payment_amount' => $order->total_amount
            ]);
        } else {
            UpdateOrder::make()->action($order, [
                'payment_amount' => $paymentAmount
            ]);
        };

        data_forget($modelData, 'reference');
        if ($paymentAmount > $order->total_amount) {
            $excessAmount = $paymentAmount - $order->total_amount;
            data_set($modelData, 'amount', $excessAmount);
            data_set($modelData, 'type', CreditTransactionTypeEnum::TRANSFER_IN);
            data_set($modelData, 'payment_id', $payment->id);
            StoreCreditTransaction::run($order->customer, $modelData);
        }
    }

    public function rules()
    {
        return [
            'amount'    => ['required', 'numeric'],
            'reference' => ['sometimes'],
        ];
    }

    public function action(Order $order, Payment $payment, array $modelData): void
    {
        $this->asAction = true;
        $this->initialisationFromShop($order->shop, $modelData);
        $this->handle($order, $payment, $modelData);
    }
}
