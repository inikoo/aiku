<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 27 Nov 2022 13:06:27 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('fulfilment_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('number')->nullable()->index();

            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');

            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedInteger('customer_client_id')->nullable()->index();
            $table->foreign('customer_client_id')->references('id')->on('customers');


            $table->enum('state', ['submitted', 'in-warehouse', 'finalised', 'dispatched','cancelled'])->default('submitted')->index();

            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('in_warehouse_at')->nullable();
            $table->dateTimeTz('finalised_at')->nullable();
            $table->dateTimeTz('dispatched_at')->nullable();

            $table->dateTimeTz('cancelled_at')->nullable();

            $table->boolean('is_picking_on_hold')->nullable();
            $table->boolean('can_dispatch')->nullable();



            $table->jsonb('data');

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('fulfilment_orders');
    }
};
