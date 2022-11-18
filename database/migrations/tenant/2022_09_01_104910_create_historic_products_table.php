<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 01 Sept 2022 18:53:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('historic_products', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->index();
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('deleted_at')->nullable();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedDecimal('price', 18)->comment('unit price');
            $table->string('code')->nullable();
            $table->string('name', 255)->nullable();
            $table->unsignedDecimal('pack',12,3)->nullable()->comment('units per pack');
            $table->unsignedDecimal('outer',12,3)->nullable()->comment('units per outer');
            $table->unsignedDecimal('carton',12,3)->nullable()->comment('units per carton');

            $table->unsignedDecimal('cbm', 18, 4)->nullable()->comment('to be deleted');
            $table->unsignedSmallInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('central.currencies');
            $table->unsignedBigInteger('source_id')->nullable()->unique();

        });
    }


    public function down()
    {
        Schema::dropIfExists('historic_products');
    }
};
