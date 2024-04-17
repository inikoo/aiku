<?php

namespace App\Actions\Accounting\PaymentGateway\Paypal\Orders;

use App\Actions\Accounting\PaymentGateway\Paypal\Traits\WithPaypalConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class AuthorizePaymentOrderPaypal
{
    use AsAction;
    use WithPaypalConfiguration;

    public string $commandSignature   = 'paypal:authorize {orderId}';
    public string $commandDescription = 'Authorize checkout detail using paypal';

    public function handle(string $orderId, array $order)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->url() . '/v2/checkout/orders/' . $orderId . '/authorize', [
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
