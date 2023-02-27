<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 18:04:37 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('tenant_sales_stats', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');


            $table->unsignedBigInteger('number_customers')->default(0);

            $customerStates = ['in-process', 'active', 'losing', 'lost', 'registered'];
            foreach ($customerStates as $customerState) {
                $table->unsignedBigInteger('number_customers_state_'.str_replace('-', '_', $customerState))->default(0);
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


            $periods           = ['all', '1y', '1q', '1m', '1w', 'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'yda', 'tdy'];
            $periods_last_year = ['1y', '1q', '1m', '1w', 'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'yda', 'tdy'];
            $previous_years    = ['py1', 'py2', 'py3', 'py4', 'py5'];
            $previous_quarters = ['pq1', 'pq2', 'pq3', 'pq4', 'pq5'];

            foreach ($periods as $col) {
                $table->decimal($col)->default(0);
            }
            foreach ($periods_last_year as $col) {
                $table->decimal($col.'_ly')->default(0);
            }
            foreach ($previous_years as $col) {
                $table->decimal($col)->default(0);
            }
            foreach ($previous_quarters as $col) {
                $table->decimal($col)->default(0);
            }


            $table->timestampsTz();
            $table->unique(['tenant_id', 'currency_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('tenant_sales_stats');
    }
};
