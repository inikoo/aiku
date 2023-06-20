<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 25 May 2023 15:03:06 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\PaymentServiceProvider;

use App\Models\Accounting\PaymentServiceProvider;
use Lorisleiva\Actions\Concerns\AsObject;

class GetPaymentServiceProviderShowcase
{
    use AsObject;

    public function handle(PaymentServiceProvider $warehouse): array
    {
        return [
            [

            ]
        ];
    }
}
