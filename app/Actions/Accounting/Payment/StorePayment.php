<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use Lorisleiva\Actions\Concerns\AsAction;


class StorePayment
{
    use AsAction;

    public function handle(PaymentAccount $paymentAccount, array $modelData): Payment
    {
        /** @var Payment $payment */
        $payment = $paymentAccount->payments()->create($modelData);
        return $payment;
    }

}
