<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 07:21:37 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('number')->index();

            $table->unsignedMediumInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');

            $table->unsignedBigInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedBigInteger('order_id')->index();
            $table->foreign('order_id')->references('id')->on('orders');


            $table->enum('type', ['invoice', 'refund'])->index();

            $table->unsignedMediumInteger('billing_address_id')->nullable()->index();
            $table->foreign('billing_address_id')->references('id')->on('addresses');


            $table->unsignedSmallInteger('currency_id');
            $table->decimal('exchange', 16, 6)->default(1);

            $table->decimal('net', 16)->default(0);
            $table->decimal('total', 16)->default(0);
            $table->decimal('payment', 16)->default(0);


            $table->dateTimeTz('paid_at')->nullable();


            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable();
        });
    }


    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
