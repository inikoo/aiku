<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:06:44 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->foreignId('organisation_id')->constrained();
            $table->unsignedSmallInteger('warehouse_id')->index();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->unsignedSmallInteger('warehouse_area_id')->nullable()->index();
            $table->foreign('warehouse_area_id')->references('id')->on('warehouse_areas');

            $table->enum('state', ['operational', 'broken'])->index()->default('operational');
            $table->string('code', 64)->index();
            $table->boolean('is_empty')->default(true);

            $table->jsonb('data');


            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unsignedBigInteger('organisation_source_id')->nullable();
            $table->unique(['organisation_id','organisation_source_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('locations');
    }
};
