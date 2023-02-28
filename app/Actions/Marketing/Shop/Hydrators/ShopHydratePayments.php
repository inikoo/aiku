<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 01:07:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Shop\Hydrators;

use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;


class ShopHydratePayments implements ShouldBeUnique
{

    use AsAction;

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


        $shop->accountingStats()->update($stats);
    }

    public function getJobUniqueId(Shop $shop): string
    {
        return $shop->id;
    }


}


