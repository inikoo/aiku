<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 27 Nov 2022 13:06:27 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('fulfilment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('number')->nullable()->index();

            $table->unsignedMediumInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');

            $table->unsignedBigInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedBigInteger('customer_client_id')->nullable()->index();
            $table->foreign('customer_client_id')->references('id')->on('customers');


            $table->enum('state', ['submitted', 'in-warehouse', 'finalised', 'dispatched'])->default('submitted')->index();

            $table->boolean('is_picking_on_hold')->nullable();
            $table->boolean('can_dispatch')->nullable();


            $table->unsignedMediumInteger('delivery_address_id')->nullable()->index();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');
            $table->jsonb('data');
            $table->dateTimeTz('sent_warehouse_at')->nullable();
            $table->dateTimeTz('ready_to_dispatch_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('fulfilment_orders');
    }
};
