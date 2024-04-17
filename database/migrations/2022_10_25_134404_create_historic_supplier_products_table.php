<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:17:37 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('historic_supplier_products', function (Blueprint $table) {
            $table->increments('id');


            $table->string('slug')->unique()->collation('und_ns');
            $table->boolean('status')->index();
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('deleted_at')->nullable();
            $table->unsignedInteger('supplier_product_id')->nullable()->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');

            $table->decimal('cost', 18, 4)->comment('unit cost');
            $table->string('code')->nullable();
            $table->string('name', 255)->nullable();
            $table->unsignedInteger('units_per_pack')->nullable();
            $table->unsignedInteger('units_per_carton')->nullable();
            $table->decimal('cbm', 18, 4)->nullable();

            $table->unsignedSmallInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies');

            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('historic_supplier_products');
    }
};
