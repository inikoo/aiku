<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:01:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('warehouse_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->foreignId('organisation_id')->constrained();
            $table->unsignedSmallInteger('warehouse_id')->index();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->unsignedSmallInteger('number_warehouse_areas')->default(0);
            $table->unsignedSmallInteger('number_locations')->default(0);
            $table->unsignedMediumInteger('number_locations_state_operational')->default(0);
            $table->unsignedMediumInteger('number_locations_state_broken')->default(0);
            $table->unsignedSmallInteger('number_empty_locations')->default(0);


            $table->decimal('stock_value', 16)->default(0);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('warehouse_stats');
    }
};
