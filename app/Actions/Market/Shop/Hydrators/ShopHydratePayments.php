<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 01:07:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Shop\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Models\Accounting\Payment;
use App\Models\Market\Shop;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydratePayments implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Shop $shop): void
    {
        $paymentRecords = $shop->payments()->count();
        $refunds        = $shop->payments()->where('type', 'refund')->count();

        $amountTenantCurrencySuccessfullyPaid = $shop->payments()
            ->where('type', 'payment')
            ->where('status', 'success')
            ->sum('tc_amount');
        $amountTenantCurrencyRefunded         = $shop->payments()
            ->where('type', 'refund')
            ->where('status', 'success')
            ->sum('tc_amount');

        $amountSuccessfullyPaid = $shop->payments()
            ->where('type', 'payment')
            ->where('status', 'success')
            ->sum('amount');
        $amountRefunded         = $shop->payments()
            ->where('type', 'refund')
            ->where('status', 'success')
            ->sum('amount');


        $stats = [
            'number_payment_records'      => $paymentRecords,
            'number_payments'             => $paymentRecords - $refunds,
            'number_refunds'              => $refunds,
            'amount'                      => $amountSuccessfullyPaid + $amountTenantCurrencyRefunded,
            'amount_successfully_paid'    => $amountSuccessfullyPaid,
            'amount_refunded'             => $amountRefunded,
            'tc_amount'                   => $amountTenantCurrencySuccessfullyPaid + $amountTenantCurrencyRefunded,
            'tc_amount_successfully_paid' => $amountTenantCurrencySuccessfullyPaid,
            'tc_amount_refunded'          => $amountTenantCurrencyRefunded


        ];

        $stateCounts = Payment::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_payment_records_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $stateCounts = Payment::where('shop_id', $shop->id)->where('type', 'payment')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_payments_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $stateCounts = Payment::where('shop_id', $shop->id)->where('type', 'refund')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_refunds_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }


        $shop->accountingStats()->update($stats);
    }

    public function getJobUniqueId(Shop $shop): string
    {
        return $shop->id;
    }
}
