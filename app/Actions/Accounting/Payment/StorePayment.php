<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Sales\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class StorePayment
{
    use AsAction;

    public function handle(PaymentAccount|Customer $parent, array $modelData): Payment
    {
        if (class_basename($parent)=='Customer') {
            $modelData['shop_id']=$parent->shop_id;
        }

        /** @var Payment $payment */
        $payment = $parent->payments()->create($modelData);
        return $payment;
    }
}
