<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:45:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Accounting\Payment\PaymentStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasPaymentStats
{
    public function paymentStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_payment_records')->default(0);
        $table->unsignedInteger('number_payments')->default(0);
        $table->unsignedInteger('number_refunds')->default(0);


        if ($table->getTable()=='shop_accounting_stats') {
            $table->decimal('amount', 16)->comment('amount_successfully_paid-amount_returned')->default(0);
            $table->decimal('amount_successfully_paid', 16)->default(0);
            $table->decimal('amount_refunded', 16)->default(0);
        }

        $table->decimal('tc_amount', 16)->comment('tenant currency, amount_successfully_paid-amount_returned')->default(0);
        $table->decimal('tc_amount_successfully_paid', 16)->default(0);
        $table->decimal('tc_amount_refunded', 16)->default(0);

        $table->decimal('gc_amount', 16)->comment('Group currency, amount_successfully_paid-amount_returned')->default(0);
        $table->decimal('gc_amount_successfully_paid', 16)->default(0);
        $table->decimal('gc_amount_refunded', 16)->default(0);


        foreach (PaymentStateEnum::cases() as $state) {
            $table->unsignedInteger("number_payment_records_state_{$state->snake()}")->default(0);
            $table->unsignedInteger("number_payments_state_{$state->snake()}")->default(0);
            $table->unsignedInteger("number_refunds_state_{$state->snake()}")->default(0);
        }

        return $table;
    }
}
