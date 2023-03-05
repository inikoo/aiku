<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:30:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\WithActionUpdate;
use App\Models\Accounting\PaymentAccount;

class UpdatePaymentAccount
{
    use WithActionUpdate;

    public function handle(PaymentAccount $paymentAccount, array $modelData): PaymentAccount
    {
        return $this->update($paymentAccount, $modelData, ['data']);
    }
}
