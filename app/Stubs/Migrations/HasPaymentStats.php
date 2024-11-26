<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:45:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentTypeEnum;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasPaymentStats
{
    public function paymentServiceProviderStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_org_payment_service_providers')->default(0);
        foreach (PaymentServiceProviderTypeEnum::cases() as $case) {
            $table->unsignedInteger("number_org_payment_service_providers_type_{$case->snake()}")->default(0);
        }
        return $table;
    }

    public function paymentAccountStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_payment_accounts')->default(0);
        foreach (PaymentAccountTypeEnum::cases() as $case) {
            $table->unsignedInteger("number_payment_accounts_type_{$case->snake()}")->default(0);
        }
        return $table;
    }

    public function paymentStats(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_payments')->default(0);
        foreach (PaymentTypeEnum::cases() as $case) {
            $table->unsignedInteger("number_payments_type_{$case->snake()}")->default(0);
        }
        foreach (PaymentStateEnum::cases() as $state) {
            $table->unsignedInteger("number_payments_state_{$state->snake()}")->default(0);
        }
        foreach (PaymentTypeEnum::cases() as $type) {
            foreach (PaymentStateEnum::cases() as $state) {
                $table->unsignedInteger("number_payments_type_{$type->snake()}_state_{$state->snake()}")->default(0);
            }
        }


        if ($table->getTable() == 'shop_accounting_stats') {
            $table->decimal('amount', 16)->comment('amount_successfully_paid-amount_returned')->default(0);
            $table->decimal('amount_successfully_paid', 16)->default(0);
            $table->decimal('amount_refunded', 16)->default(0);
        }

        $table->decimal('org_amount', 16)->comment('organisation currency, amount_successfully_paid-amount_returned')->default(0);
        $table->decimal('org_amount_successfully_paid', 16)->default(0);
        $table->decimal('org_amount_refunded', 16)->default(0);

        $table->decimal('group_amount', 16)->comment('Group currency, amount_successfully_paid-amount_returned')->default(0);
        $table->decimal('group_amount_successfully_paid', 16)->default(0);
        $table->decimal('group_amount_refunded', 16)->default(0);


        return $table;
    }
}
