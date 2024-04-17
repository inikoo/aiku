<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:33:34 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentGateway\Checkout\Channels;

use App\Models\Accounting\Payment;
use Checkout\CheckoutSdk;
use Checkout\Customers\CustomerRequest;
use Checkout\Environment;
use Checkout\Payments\Request\PaymentRequest;
use Checkout\Payments\Request\Source\RequestCardSource;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\WithAttributes;

class MakePaymentUsingCheckout
{
    use AsAction;
    use WithAttributes;
    use AsCommand;

    private bool $asAction=false;

    public string $commandSignature = 'payment:checkout {payment}';

    /**
     * @throws \Checkout\CheckoutArgumentException
     * @throws \Checkout\CheckoutApiException
     */
    public function handle(Payment $payment): array
    {
        $checkoutApi = CheckoutSdk::builder()->staticKeys()
            ->publicKey(Arr::get($payment->paymentAccount->data, 'checkout_public_key'))
            ->secretKey(Arr::get($payment->paymentAccount->data, 'checkout_secret_key'))
            ->environment(app()->isProduction() ? Environment::production() : Environment::sandbox())
            ->build();

        $paymentsClient  = $checkoutApi->getPaymentsClient();
        $paymentRequest  = new PaymentRequest();
        $customerRequest = new CustomerRequest();

        $requestCardSource                  = new RequestCardSource();
        $requestCardSource->name            = Arr::get($payment->data, 'card_name');
        $requestCardSource->number          = Arr::get($payment->data, 'card_number');
        $requestCardSource->expiry_year     = Arr::get($payment->data, 'card_expiry_year');
        $requestCardSource->expiry_month    = Arr::get($payment->data, 'card_expiry_month');
        $requestCardSource->cvv             = Arr::get($payment->data, 'card_cvv');

        $customerRequest->name  = $payment->customer->name;
        $customerRequest->email = $payment->customer->email;

        $paymentRequest->source                = $requestCardSource;
        $paymentRequest->amount                = $payment->amount;
        $paymentRequest->currency              = $payment->currency->code;
        $paymentRequest->capture               = true;
        $paymentRequest->success_url           = "https://testing.checkout.com/sucess";
        $paymentRequest->failure_url           = "https://testing.checkout.com/failure";
        $paymentRequest->customer              = $customerRequest;
        $paymentRequest->processing_channel_id = Arr::get($payment->paymentAccount->data, 'checkout_channel_id');

        return $paymentsClient->requestPayment($paymentRequest);
    }

    /**
     * @throws \Checkout\CheckoutArgumentException
     * @throws \Checkout\CheckoutApiException
     */
    public function asCommand(Command $command): int
    {
        $payment = $command->argument('payment');
        $payment = Payment::where('slug', $payment)->first();

        $this->handle($payment);

        return 0;
    }
}
