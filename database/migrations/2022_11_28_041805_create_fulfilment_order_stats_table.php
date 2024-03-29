<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 28 Nov 2022 12:25:28 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('fulfilment_order_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fulfilment_order_id')->index();
            $table->foreign('fulfilment_order_id')->references('id')->on('fulfilment_orders');
            $table->unsignedSmallInteger('number_items_at_creation')->default(0);
            $table->unsignedSmallInteger('number_cancelled_items')->default(0);
            $table->unsignedSmallInteger('number_add_up_items')->default(0);
            $table->unsignedSmallInteger('number_cut_off_items')->default(0);
            $table->unsignedSmallInteger('number_items_fulfilled')->default(0);
            $table->unsignedSmallInteger('number_items')->default(0)->comment('current number of items');

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('fulfilment_order_stats');
    }
};
