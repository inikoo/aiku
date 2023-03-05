<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 28 Nov 2022 12:04:07 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('fulfilment_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedBigInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedBigInteger('fulfilment_order_id')->index();
            $table->foreign('fulfilment_order_id')->references('id')->on('fulfilment_orders');

            $table->enum('state', ['submitted', 'in-warehouse','packed', 'finalised', 'dispatched'])->default('in-process')->index();
            $table->nullableMorphs('item');
            $table->decimal('quantity', 16, 3);

            $table->string('notes')->nullable();

            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('fulfilment_order_items');
    }
};
