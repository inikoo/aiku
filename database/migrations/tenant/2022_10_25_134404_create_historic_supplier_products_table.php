<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 15:04:59 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('historic_supplier_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->boolean('status')->index();
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('deleted_at')->nullable();
            $table->unsignedInteger('supplier_product_id')->nullable()->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');

            $table->unsignedDecimal('cost', 18, 4)->comment('unit cost');
            $table->string('code')->nullable();
            $table->string('name', 255)->nullable();
            $table->unsignedSmallInteger('units_per_pack')->nullable();
            $table->unsignedSmallInteger('units_per_carton')->nullable();
            $table->unsignedDecimal('cbm', 18, 4)->nullable();

            $table->unsignedSmallInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('central.currencies');

            $table->unsignedSmallInteger('central_historic_supplier_product_id')->nullable();
            $table->foreign('central_historic_supplier_product_id')->references('id')->on('central.central_historic_supplier_products');


            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('historic_supplier_products');
    }
};
