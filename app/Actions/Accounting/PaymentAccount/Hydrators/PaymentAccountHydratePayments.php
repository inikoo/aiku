<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:37:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\PaymentStateEnum;
use App\Models\Accounting\PaymentAccount;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;


class PaymentAccountHydratePayments implements ShouldBeUnique
{

    use AsAction;
    use WithTenantJob;

    public function handle(PaymentAccount $paymentAccount): void
    {
        $paymentRecords = $paymentAccount->payments()->count();
        $refunds        = $paymentAccount->payments()->where('type', 'refund')->count();

        $dCAmountSuccessfullyPaid = $paymentAccount->payments()
            ->where('type', 'payment')
            ->where('status', 'success')
            ->sum('dc_amount');
        $dCAmountRefunded         = $paymentAccount->payments()
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

        $stateCounts =$paymentAccount->payments()
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::valuesDB() as $state) {
            $stats["number_payment_records_state_$state"] = Arr::get($stateCounts, $state, 0);
        }

        $stateCounts =$paymentAccount->payments()->where('type','payment')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::valuesDB() as $state) {
            $stats["number_payments_state_$state"] = Arr::get($stateCounts, $state, 0);
        }

        $stateCounts =$paymentAccount->payments()->where('type','refund')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::valuesDB() as $state) {
            $stats["number_refunds_state_$state"] = Arr::get($stateCounts, $state, 0);
        }

        $paymentAccount->stats->update($stats);
    }

    public function getJobUniqueId(PaymentAccount $paymentAccount): int
    {
        return $paymentAccount->id;
    }


}


