<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider;

use App\Models\Accounting\PaymentServiceProvider;
use Lorisleiva\Actions\Concerns\AsAction;


class StorePaymentServiceProvider
{
    use AsAction;

    public function handle(array $modelData): PaymentServiceProvider
    {
        /** @var PaymentServiceProvider $paymentServiceProvider */
        $paymentServiceProvider = PaymentServiceProvider::create($modelData);
        $paymentServiceProvider->stats()->create();
        return $paymentServiceProvider;
    }

}
