<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 07:21:37 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('number')->index();
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedInteger('order_id')->index();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->string('type')->index();
            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('central.currencies');
            $table->decimal('exchange', 16, 6)->default(1);
            $table->decimal('net', 16)->default(0);
            $table->decimal('total', 16)->default(0);
            $table->decimal('payment', 16)->default(0);
            $table->dateTimeTz('paid_at')->nullable();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->index()->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
