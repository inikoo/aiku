<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 01:07:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Shop\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\PaymentStateEnum;
use App\Models\Accounting\Payment;
use App\Models\Marketing\Shop;
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

        $dCAmountSuccessfullyPaid = $shop->payments()
            ->where('type', 'payment')
            ->where('status', 'success')
            ->sum('dc_amount');
        $dCAmountRefunded         = $shop->payments()
            ->where('type', 'refund')
            ->where('status', 'success')
            ->sum('dc_amount');

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
            'amount'                      => $amountSuccessfullyPaid + $dCAmountRefunded,
            'amount_successfully_paid'    => $amountSuccessfullyPaid,
            'amount_refunded'             => $amountRefunded,
            'dc_amount'                   => $dCAmountSuccessfullyPaid + $dCAmountRefunded,
            'dc_amount_successfully_paid' => $dCAmountSuccessfullyPaid,
            'dc_amount_refunded'          => $dCAmountRefunded


        ];

        $stateCounts = Payment::where('shop_id', $shop->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::valuesDB() as $state) {
            $stats["number_payment_records_state_$state"] = Arr::get($stateCounts, $state, 0);
        }

        $stateCounts = Payment::where('shop_id', $shop->id)->where('type','payment')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::valuesDB() as $state) {
            $stats["number_payments_state_$state"] = Arr::get($stateCounts, $state, 0);
        }

        $stateCounts = Payment::where('shop_id', $shop->id)->where('type','refund')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::valuesDB() as $state) {
            $stats["number_refunds_state_$state"] = Arr::get($stateCounts, $state, 0);
        }


        $shop->accountingStats()->update($stats);
    }

    public function getJobUniqueId(Shop $shop): string
    {
        return $shop->id;
    }


}


