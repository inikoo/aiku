<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 13:57:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores');
            $table->unsignedMediumInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedMediumInteger('billing_id')->nullable()->index();
            $table->foreign('billing_id')->references('id')->on('addresses');
            $table->unsignedMediumInteger('delivery_id')->nullable()->index();
            $table->foreign('delivery_id')->references('id')->on('addresses');


            $table->string('number')->index();
            $table->string('state')->nullable()->index();
            $table->string('status')->index();

            $table->string('payment_status')->nullable()->index();

            $table->decimal('net', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('payment', 16, 2)->default(0);

            $table->decimal('weight', 16, 2)->default(0);
            $table->unsignedMediumInteger('items')->default(0);

            $table->dateTimeTz('date', 0)->index();
            $table->dateTimeTz('submitted_at', 0)->nullable();
            $table->dateTimeTz('warehoused_at', 0)->nullable();
            $table->dateTimeTz('picking_at', 0)->nullable();
            $table->dateTimeTz('packed_at', 0)->nullable();
            $table->dateTimeTz('invoiced_at', 0)->nullable();
            $table->dateTimeTz('dispatched_at', 0)->nullable();
            $table->dateTimeTz('cancelled_at', 0)->nullable();

            $table->jsonb('data');
            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
