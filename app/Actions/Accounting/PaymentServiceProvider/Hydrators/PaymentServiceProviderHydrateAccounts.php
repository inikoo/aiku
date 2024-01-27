<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:26:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider\Hydrators;

use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentServiceProviderHydrateAccounts
{
    use AsAction;

    private PaymentServiceProvider $paymentServiceProvider;

    public function __construct(PaymentServiceProvider $paymentServiceProvider)
    {
        $this->paymentServiceProvider = $paymentServiceProvider;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->paymentServiceProvider->id))->dontRelease()];
    }

    public function handle(PaymentServiceProvider $paymentServiceProvider): void
    {
        $stats=[
            'number_payment_accounts'=> $paymentServiceProvider->accounts()->count()
        ];
        $paymentServiceProvider->stats()->update($stats);
    }


}
