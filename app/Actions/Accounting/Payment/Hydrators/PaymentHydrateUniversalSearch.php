<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\Hydrators;

use App\Actions\WithRoutes;
use App\Actions\WithTenantJob;
use App\Models\Accounting\Payment;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;
    use WithRoutes;

    public function handle(Payment $payment): void
    {
        $payment->universalSearch()->create(
            [
                'section' => 'Accounting',
                'route' => $this->routes(),
                'icon' => 'fa-coins',
                'primary_term'   => $payment->amount.' '.$payment->reference,
                'secondary_term' => $payment->customer_id.' '.$payment->date
            ]
        );
    }

}
