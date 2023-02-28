<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:31:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\WithActionUpdate;
use App\Models\Accounting\Payment;


class UpdatePayment
{
    use WithActionUpdate;

    public function handle(Payment $payment, array $modelData): Payment
    {
        return $this->update($payment, $modelData, ['data']);
    }
}
