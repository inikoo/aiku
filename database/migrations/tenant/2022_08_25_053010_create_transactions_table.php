<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 27 Aug 2022 23:08:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedInteger('order_id')->index();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->enum('state', ['submitted', 'in-warehouse', 'packed', 'finalised', 'no-dispatched', 'dispatched', 'cancelled'])->default('submitted')->nullable()->index();

            $table->nullableMorphs('item');
            $table->decimal('quantity', 16, 3);
            $table->decimal('discounts', 16)->default(0);
            $table->decimal('net', 16)->default(0);
            $table->unsignedSmallInteger('tax_band_id')->nullable()->index();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
