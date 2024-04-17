<?php

namespace App\Actions\Accounting\PaymentGateway\Paypal\Orders;

use App\Actions\Accounting\PaymentGateway\Paypal\Traits\WithPaypalConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class ConfirmOrderPaypal
{
    use AsAction;
    use WithPaypalConfiguration;

    public string $commandSignature   = 'paypal:confirm {orderId}';
    public string $commandDescription = 'Confirm checkout detail using paypal';

    public function handle(string $orderId)
    {
        $response = Http::withHeaders($this->headers())->post($this->url() . '/v2/checkout/orders/' . $orderId . '/confirm-payment-source');

        return $response->json();
    }

    public function asCommand(Command $command)
    {
        dd($this->handle($command->argument('orderId')));
    }
}
