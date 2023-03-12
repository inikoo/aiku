<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 23:08:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Models\Traits\Stubs\HasDateIntervalsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDateIntervalsStats;

    public function up()
    {
        Schema::create('tenant_sales_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');


            $table->unsignedBigInteger('number_customers')->default(0);


            foreach (CustomerStateEnum::cases() as $customerState) {
                $table->unsignedInteger("number_customers_state_{$customerState->snake()}")->default(0);
            }


            $customerTradeStates = ['none', 'one', 'many'];
            foreach ($customerTradeStates as $customerTradeState) {
                $table->unsignedBigInteger('number_customers_trade_state_'.$customerTradeState)->default(0);
            }

            $table->unsignedBigInteger('number_orders')->default(0);
            $orderStates = ['in-basket', 'in-process', 'in-warehouse', 'packed', 'finalised', 'dispatched', 'returned', 'cancelled'];
            foreach ($orderStates as $orderState) {
                $table->unsignedBigInteger('number_orders_state_'.str_replace('-', '_', $orderState))->default(0);
            }


            $table->unsignedBigInteger('number_invoices')->default(0);
            $table->unsignedBigInteger('number_invoices_type_invoice')->default(0);
            $table->unsignedBigInteger('number_invoices_type_refund')->default(0);


            $table->unsignedSmallInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies');

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
