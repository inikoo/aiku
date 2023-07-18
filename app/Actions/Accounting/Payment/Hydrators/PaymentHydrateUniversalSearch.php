<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Accounting\Payment;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Payment $payment): void
    {
        $payment->universalSearch()->updateOrCreate(
            [],
            [
                'section' => 'accounting',
                'title'   => $payment->reference,
            ]
        );
    }

}
