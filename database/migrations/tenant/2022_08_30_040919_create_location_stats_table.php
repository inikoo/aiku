<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:09:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('location_stats', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('location_id')->index();
            $table->foreign('location_id')->references('id')->on('locations');

            $table->unsignedSmallInteger('number_stock_slots')->default(0);
            $table->unsignedSmallInteger('number_empty_stock_slots')->default(0);
            $table->decimal('stock_value', 16)->default(0);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('location_stats');
    }
};
