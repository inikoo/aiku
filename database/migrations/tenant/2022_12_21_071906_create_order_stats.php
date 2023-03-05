<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Dec 2022 15:19:17 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('order_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->unsignedSmallInteger('number_items_at_creation')->default(0);
            $table->unsignedSmallInteger('number_cancelled_items')->default(0);
            $table->unsignedSmallInteger('number_add_up_items')->default(0);
            $table->unsignedSmallInteger('number_cut_off_items')->default(0);
            $table->unsignedSmallInteger('number_items_dispatched')->default(0);
            $table->unsignedSmallInteger('number_items')->default(0)->comment('current number of items');

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('order_stats');
    }
};
