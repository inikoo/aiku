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

        $paymentRecords = $paymentServiceProvider->payments()->count();
        $refunds        = $paymentServiceProvider->payments()->where('type', 'refund')->count();

        $dCAmountSuccessfullyPaid = $paymentServiceProvider->payments()
            ->where('type', 'payment')
            ->where('status', 'success')
            ->sum('dc_amount');
        $dCAmountRefunded         = $paymentServiceProvider->payments()
            ->where('type', 'refund')
            ->where('status', 'success')
            ->sum('dc_amount');

        $stats = [
            'number_payment_records'      => $paymentRecords,
            'number_payments'             => $paymentRecords - $refunds,
            'number_refunds'              => $refunds,
            'dc_amount'                   => $dCAmountSuccessfullyPaid + $dCAmountRefunded,
            'dc_amount_successfully_paid' => $dCAmountSuccessfullyPaid,
            'dc_amount_refunded'          => $dCAmountRefunded


        ];


        $paymentServiceProvider->stats->update($stats);
    }

    public function getJobUniqueId(PaymentServiceProvider $paymentServiceProvider): int
    {
        return $paymentServiceProvider->id;
    }


}


