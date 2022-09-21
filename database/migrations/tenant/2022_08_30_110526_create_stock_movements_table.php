<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:05:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['purchase', 'return', 'delivery', 'lost', 'found', 'location-transfer', 'cancelled-to-restock', 'cancelled-restocked', 'amendment', 'consumption'])->index();
            $table->morphs('stockable');
            $table->unsignedMediumInteger('location_id')->nullable()->index();
            $table->foreign('location_id')->references('id')->on('locations');

            $table->decimal('quantity', 16, 3);
            $table->decimal('amount', 16, 3);
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unsignedBigInteger('source_id')->nullable()->nullable()->index();

        });
    }


    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
};
