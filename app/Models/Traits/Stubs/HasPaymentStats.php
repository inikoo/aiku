<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Feb 2023 23:51:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Models\Traits\Stubs;

use App\Enums\PaymentStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasPaymentStats
{

    function paymentStats(Blueprint $table): Blueprint
    {
        $table->unsignedBigInteger('number_payment_records')->default(0);
        $table->unsignedBigInteger('number_payments')->default(0);
        $table->unsignedBigInteger('number_refunds')->default(0);


        if($table->getTable()=='shop_accounting_stats'){
            $table->decimal('amount',16)->comment('amount_successfully_paid-amount_returned')->default(0);
            $table->decimal('amount_successfully_paid',16)->default(0);
            $table->decimal('amount_refunded',16)->default(0);
        }

        $table->decimal('dc_amount',16)->comment('Account currency, amount_successfully_paid-amount_returned')->default(0);
        $table->decimal('dc_amount_successfully_paid',16)->default(0);
        $table->decimal('dc_amount_refunded',16)->default(0);


        foreach (PaymentStateEnum::valuesDB() as $state) {
            $table->unsignedBigInteger("number_payment_records_state_{$state}")->default(0);
            $table->unsignedBigInteger("number_payments_state_{$state}")->default(0);
            $table->unsignedBigInteger("number_refunds_state_{$state}")->default(0);
        }

        return $table;
    }

}

