<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 20:10:10 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider\UI;

use App\Models\Accounting\PaymentServiceProvider;
use Lorisleiva\Actions\Concerns\AsObject;

class GetPaymentServiceProviderShowcase
{
    use AsObject;

    public function handle(PaymentServiceProvider $paymentServiceProvider): array
    {
        return [
            [

            ]
        ];
    }
}
