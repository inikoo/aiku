<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:37:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount\Hydrators;

use App\Models\Accounting\PaymentAccount;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;


class PaymentAccountHydratePayments implements ShouldBeUnique
{

    use AsAction;

    public function handle(PaymentAccount $paymentAccount): void
    {
        $stats=[
            'number_accounts'=>$paymentAccount->payments()->count()
        ];
        $paymentAccount->stats->update($stats);
    }

    public function getJobUniqueId(PaymentAccount $paymentAccount): int
    {
        return $paymentAccount->id;
    }


}


