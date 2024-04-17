<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:33:34 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentGateway\Xendit\Channels\Invoice;

use App\Models\Accounting\Payment;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\Invoice;
use Xendit\Invoice\InvoiceApi;

class MakePaymentUsingXendit
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    /**
     * @throws \Xendit\XenditSdkException
     */
    public function handle(Payment $payment): Invoice
    {
        Configuration::setXenditKey(Arr::get($payment->paymentAccount->data, 'api_key'));
        $invoice = new InvoiceApi();

        $customer   = $payment->customer;
        $externalId = $payment->reference;

        $params = new CreateInvoiceRequest([
            'external_id'      => $externalId,
            'amount'           => (int) $payment->amount,
            'description'      => 'Invoice for ' . $customer->name,
            'invoice_duration' => 3600,
            'customer'         => [
                'given_names'   => $customer->name,
                'email'         => $customer->email
            ],
            'success_redirect_url' => route('customer.billing.dashboard'),
            'failure_redirect_url' => route('customer.billing.dashboard')
        ]);

        $response = $invoice->createInvoice($params);
        $payment->update(['data' => $response]);

        return $response;
    }
}
