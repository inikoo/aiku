<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 18:36:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();


            $table->string('slug')->unique();
            $table->string('code')->index();
            $table->morphs('owner');
            $table->unsignedSmallInteger('stock_family_id')->index()->nullable();
            $table->foreign('stock_family_id')->references('id')->on('stock_families');
            $table->enum('composition', ['unit', 'multiple', 'mix'])->default('unit');
            $stockStates = ['in-process', 'active', 'discontinuing', 'discontinued'];

            $table->enum('state', $stockStates)->nullable()->index();
            $table->enum('quantity_status', ['surplus', 'optimal', 'low', 'critical', 'out-of-stock', 'error'])->nullable()->index();
            $table->boolean('sellable')->default(1)->index();
            $table->boolean('raw_material')->default(0)->index();

            $table->string('barcode')->index()->nullable();
            $table->text('description')->nullable();
            $table->unsignedMediumInteger('pack')->nullable()->comment('units per pack');
            $table->unsignedMediumInteger('outer')->nullable()->comment('units per outer');
            $table->unsignedMediumInteger('carton')->nullable()->comment('units per carton');
            $table->decimal('quantity', 16, 3)->nullable()->default(0)->comment('stock quantity in units');
            $table->float('available_forecast')->nullable()->comment('days');
            $table->decimal('value', 16)->nullable();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->unsignedBigInteger('package_image_id')->nullable();
            $table->jsonb('settings');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->dateTimeTz('activated_at')->nullable();
            $table->dateTimeTz('discontinuing_at')->nullable();
            $table->dateTimeTz('discontinued_at')->nullable();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};
