<?php

namespace App\Actions\Accounting\PaymentGateway\Paypal\Orders;

use App\Actions\Accounting\PaymentGateway\Paypal\Traits\WithPaypalConfiguration;
use App\Models\Accounting\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreOrderToPaypal
{
    use AsAction;
    use WithPaypalConfiguration;

    public string $commandSignature   = 'paypal:checkout';
    public string $commandDescription = 'Checkout using paypal';

    public function handle(Payment $payment, array $orderData)
    {
        $itemTotalAmount = 0;
        $discount        = 0;
        $items           = [];

        foreach ($orderData['items'] as $item) {
            $items[] = [
                "name"        => $item['name'],
                "quantity"    => $item['quantity'],
                "description" => $item['description'],
                "sku"         => $item['sku'],
                "unit_amount" => [
                    "currency_code" => $orderData['currency_code'],
                    "value"         => $item['amount']
                ]
            ];

            $itemTotalAmount += $item['amount'] * $item['quantity'];
        }

        $response = Http::withHeaders($this->headers(
            Arr::get($payment->paymentAccount->data, 'paypal_client_id'),
            Arr::get($payment->paymentAccount->data, 'paypal_client_secret')
        ))->post($this->url() . '/v2/checkout/orders', [
            "intent"         => "CAPTURE",
            'purchase_units' => [
                [
                    "amount" => [
                        "currency_code" => $orderData['currency_code'],
                        "value"         => $itemTotalAmount - $discount,
                        "breakdown"     => [
                            "item_total" => [
                                "currency_code" => $orderData['currency_code'],
                                "value"         => $itemTotalAmount
                            ]
                        ]
                    ],
                    'items'          => $items,
                    'payment_source' => [
                        'paypal' => [
                            'experience_context' => [
                                "payment_method_preference" => "IMMEDIATE_PAYMENT_REQUIRED",
                                "payment_method_selected"   => "PAYPAL",
                                "shipping_preference"       => "SET_PROVIDED_ADDRESS",
                                "user_action"               => "PAY_NOW"
                            ]
                        ]
                    ],
                    'application_context' => [
                        'cancel_url' => url('/payment/cancel'),
                        'return_url' => url('/payment/execute')
                    ]
                ]
            ]
        ]);

        return $response->json();
    }

    public function asCommand()
    {
        $data = [
            "currency_code" => "USD",
            "items"         => [
                [
                    "name"        => "Aw France",
                    "quantity"    => 2,
                    "description" => "Test",
                    "sku"         => "AW-12332324343",
                    "amount"      => 50
                ]
            ]
        ];

        dd($this->handle($data));
    }
}
