<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Apr 2023 13:47:44 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Enums\Sales\Customer\CustomerTradeStateEnum;
use App\Enums\Sales\Order\OrderStateEnum;
use App\Stubs\Migrations\HasDateIntervalsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDateIntervalsStats;

    public function up(): void
    {
        Schema::create('tenant_sales_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');


            $table->unsignedBigInteger('number_customers')->default(0);


            foreach (CustomerStateEnum::cases() as $customerState) {
                $table->unsignedInteger("number_customers_state_{$customerState->snake()}")->default(0);
            }


            foreach (CustomerTradeStateEnum::cases() as $tradeState) {
                $table->unsignedBigInteger('number_customers_trade_state_'.$tradeState->snake())->default(0);
            }

            $table->unsignedBigInteger('number_orders')->default(0);
            foreach (OrderStateEnum::cases() as $orderState) {
                $table->unsignedBigInteger('number_orders_state_'.$orderState->snake())->default(0);
            }


            $table->unsignedBigInteger('number_invoices')->default(0);
            $table->unsignedBigInteger('number_invoices_type_invoice')->default(0);
            $table->unsignedBigInteger('number_invoices_type_refund')->default(0);


            $table->unsignedSmallInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('public.currencies');

            $table=$this->dateIntervals($table);

            $table->timestampsTz();
            $table->unique(['tenant_id', 'currency_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('tenant_sales_stats');
    }
};
