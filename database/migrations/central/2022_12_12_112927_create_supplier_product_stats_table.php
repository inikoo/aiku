<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 18:24:30 Malaysia Time, Bali Airport
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('supplier_product_stats', function (Blueprint $table) {
            $table->increments('id');
//
//            $table->unsignedSmallInteger('group_id');
//            $table->foreign('group_id')->references('id')->on('groups');
//            $table->unsignedSmallInteger('tenant_id');
//            $table->foreign('tenant_id')->references('id')->on('tenants');

            $table->unsignedInteger('supplier_product_id')->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('supplier_product_stats');
    }
};
