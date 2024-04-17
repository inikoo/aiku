<?php

namespace App\Actions\Accounting\PaymentGateway\Paypal\Orders;

use App\Actions\Accounting\PaymentGateway\Paypal\Traits\WithPaypalConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class CapturePaymentOrderPaypal
{
    use AsAction;
    use WithPaypalConfiguration;

    public string $commandSignature   = 'paypal:capture {orderId}';
    public string $commandDescription = 'Capture checkout detail using paypal';

    public function handle(string $orderId, array $order)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->url() . '/v2/checkout/orders/' . $orderId . '/capture', [
                'payment_source' => [
                    'card' => [
                        'name'          => $order['card']['name'],
                        'number'        => $order['card']['number'],
                        'security_code' => $order['card']['security_code'],
                        'expiry'        => $order['card']['expiry']
                    ]
                ]
            ]);

        return $response->json();
    }

    public function asCommand(Command $command)
    {
        $order = [
            'card' => [
                'name'          => 'Jhon',
                'number'        => '4915805038587737',
                'security_code' => '888',
                'expiry'        => '2025-03'
            ]
        ];
        dd($this->handle($command->argument('orderId'), $order));
    }
}
