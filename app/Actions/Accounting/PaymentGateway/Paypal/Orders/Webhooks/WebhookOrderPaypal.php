<?php

namespace App\Actions\Accounting\PaymentGateway\Paypal\Orders\Webhooks;

use App\Actions\Accounting\PaymentGateway\Paypal\Traits\WithPaypalConfiguration;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class WebhookOrderPaypal
{
    use AsAction;
    use WithPaypalConfiguration;

    public function handle(array $objectData)
    {
        $orderId = $objectData['resource']['id'];
        $status  = $objectData['resource']['status'];

        // TODO Update the payment detail in database

        return $objectData;
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }
}
