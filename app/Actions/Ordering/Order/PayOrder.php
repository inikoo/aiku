<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Accounting\Payment\StorePayment;
use App\Actions\OrgAction;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentStatusEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\CRM\Customer;
use App\Models\Ordering\Order;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;

class PayOrder extends OrgAction
{
    public function handle(Order $order, Customer $customer, PaymentAccount $paymentAccount, array $modelData): Payment
    {
        foreach ($order->transactions as $index => $transaction) {
            data_set($modelData, "items.$index", [
                'name'        => $transaction->historicAsset->name,
                'quantity'    => $transaction->quantity_ordered,
                'description' => '',
                'sku'         => '',
                'amount'      => $transaction->historicAsset->price
            ]);
        }
        data_set($modelData, 'currency_code', $order->currency->code);

        $payment = StorePayment::make()->action($customer, $paymentAccount, $modelData);
        AttachPaymentToOrder::make()->action($order, $payment, [
            'amount'    => Arr::get($modelData, 'amount'),
            'reference' => Arr::get($modelData, 'reference')
        ]);

        return $payment;
    }

    public function rules()
    {
        return [
            'amount'       => ['required', 'decimal:0,2'],
            'reference'    => ['nullable', 'string', 'max:255'],
            'status'       => ['sometimes', 'required', Rule::enum(PaymentStatusEnum::class)],
            'state'        => ['sometimes', 'required', Rule::enum(PaymentStateEnum::class)],
        ];
    }

    public function action(Order $order, Customer $customer, PaymentAccount $paymentAccount, array $modelData): Payment
    {
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($order, $customer, $paymentAccount, $this->validatedData);
    }

    public function asController(Order $order, Customer $customer, PaymentAccount $paymentAccount, ActionRequest $request): Payment
    {
        $this->initialisationFromShop($customer->shop, $request);

        return $this->handle($order, $customer, $paymentAccount, $this->validatedData);
    }

    public function htmlResponse(Payment $payment): \Symfony\Component\HttpFoundation\Response
    {
        return Inertia::location(Arr::get($payment->data, 'links')[1]['href']);
    }
}
