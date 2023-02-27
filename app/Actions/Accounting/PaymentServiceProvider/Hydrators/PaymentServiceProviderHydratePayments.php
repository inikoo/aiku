<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:29:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider\Hydrators;

use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;


class PaymentServiceProviderHydratePayments implements ShouldBeUnique
{

    use AsAction;

    public function handle(PaymentServiceProvider $paymentServiceProvider): void
    {
        $stats=[
            'number_accounts'=>$paymentServiceProvider->payments()->count()
        ];
        $paymentServiceProvider->stats->update($stats);
    }

    public function getJobUniqueId(PaymentServiceProvider $paymentServiceProvider): int
    {
        return $paymentServiceProvider->id;
    }


}


