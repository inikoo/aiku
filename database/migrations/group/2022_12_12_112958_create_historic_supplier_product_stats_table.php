<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:17:52 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('historic_supplier_product_stats', function (Blueprint $table) {
            $table->increments('id');


            $table->unsignedInteger('historic_supplier_product_id')->index();
            $table->foreign('historic_supplier_product_id')->references('id')->on('historic_supplier_products');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('historic_supplier_product_stats');
    }
};
