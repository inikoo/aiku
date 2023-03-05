<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:28:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider;

use App\Actions\WithActionUpdate;
use App\Models\Accounting\PaymentServiceProvider;

class UpdatePaymentServiceProvider
{
    use WithActionUpdate;

    public function handle(PaymentServiceProvider $paymentServiceProvider, array $modelData): PaymentServiceProvider
    {
        return $this->update($paymentServiceProvider, $modelData, ['data']);
    }
}
