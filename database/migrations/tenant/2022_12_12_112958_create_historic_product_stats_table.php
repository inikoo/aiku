<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 17:55:05 Malaysia Time, Bali Airport
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('historic_supplier_product_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('historic_supplier_product_id')->index();
            $table->foreign('historic_supplier_product_id')->references('id')->on('historic_supplier_products');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('historic_supplier_product_stats');
    }
};
