<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:29:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider\Hydrators;

use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentServiceProviderHydratePayments implements ShouldBeUnique
{
    use AsAction;


    public function handle(PaymentServiceProvider $paymentServiceProvider): void
    {
        $paymentRecords = $paymentServiceProvider->payments()->count();
        $refunds        = $paymentServiceProvider->payments()->where('payments.type', 'refund')->count();

        $amountTenantCurrencySuccessfullyPaid = $paymentServiceProvider->payments()
            ->where('payments.type', 'payment')
            ->where('status', 'success')
            ->sum('tc_amount');
        $amountTenantCurrencyRefunded         = $paymentServiceProvider->payments()
            ->where('payments.type', 'refund')
            ->where('status', 'success')
            ->sum('tc_amount');

        $stats = [
            'number_payment_records'      => $paymentRecords,
            'number_payments'             => $paymentRecords - $refunds,
            'number_refunds'              => $refunds,
            'tc_amount'                   => $amountTenantCurrencySuccessfullyPaid + $amountTenantCurrencyRefunded,
            'tc_amount_successfully_paid' => $amountTenantCurrencySuccessfullyPaid,
            'tc_amount_refunded'          => $amountTenantCurrencyRefunded
        ];


        $stateCounts = $paymentServiceProvider->payments()
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_payment_records_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $stateCounts =$paymentServiceProvider->payments()->where('payments.type', 'payment')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_payments_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $stateCounts = $paymentServiceProvider->payments()->where('payments.type', 'refund')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_refunds_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $paymentServiceProvider->stats->update($stats);
    }

    public function getJobUniqueId(PaymentServiceProvider $paymentServiceProvider): int
    {
        return $paymentServiceProvider->id;
    }
}
