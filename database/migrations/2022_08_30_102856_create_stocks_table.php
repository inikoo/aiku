<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 18:36:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();
            $table->morphs('owner');
            $table->enum('composition', ['unit', 'multiple', 'mix'])->default('unit');
            $table->enum('state', ['in-process', 'active', 'discontinuing', 'discontinued'])->nullable()->index();
            $table->enum('quantity_status', ['surplus', 'optimal', 'low', 'critical', 'out-of-stock', 'error'])->nullable()->index();
            $table->boolean('sellable')->default(1)->index();
            $table->boolean('raw_material')->default(0)->index();
            $table->string('slug')->index();
            $table->string('code')->index();
            $table->string('barcode')->index()->nullable();
            $table->text('description')->nullable();
            $table->unsignedMediumInteger('pack')->nullable()->comment('units per pack');
            $table->unsignedMediumInteger('outer')->nullable()->comment('units per outer');
            $table->unsignedMediumInteger('carton')->nullable()->comment('units per carton');
            $table->decimal('quantity', 16, 3)->nullable()->comment('stock quantity in units');
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
            $table->unsignedBigInteger('organisation_source_id')->nullable();
            $table->unique(['organisation_id','organisation_source_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};
