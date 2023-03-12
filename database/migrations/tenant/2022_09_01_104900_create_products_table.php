<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 01 Sept 2022 18:55:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->nullable()->index();
            $table->morphs('owner');
            $table->unsignedInteger('current_historic_product_id')->index()->nullable();
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');

            $table->unsignedSmallInteger('family_id')->nullable();
            $table->foreign('family_id')->references('id')->on('families');


            $table->string('state')->nullable()->index();
            $table->boolean('status')->nullable()->index();
            $table->enum('composition', ['unit', 'multiple', 'mix'])->default('unit');

            $table->string('code')->index();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();

            $table->unsignedDecimal('units', 12, 3)->nullable()->comment('units per outer');
            $table->unsignedDecimal('price', 18)->comment('unit price');
            $table->unsignedDecimal('rrp', 12, 3)->nullable()->comment('RRP per outer');


            $table->unsignedInteger('available')->default(0)->nullable();
            $table->unsignedInteger('image_id')->nullable();
            $table->jsonb('settings');
            $table->jsonb('data');

            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('products');
    }
};
