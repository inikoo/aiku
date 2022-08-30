<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:40:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('warehouse_area_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('warehouse_area_id')->index();
            $table->foreign('warehouse_area_id')->references('id')->on('warehouse_areas');

            $table->unsignedSmallInteger('number_locations')->default(0);
            $table->unsignedSmallInteger('number_empty_locations')->default(0);
            $table->decimal('stock_value', 16)->default(0);
            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_area_stats');
    }
};
