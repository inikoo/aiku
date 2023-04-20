<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 19 Apr 2023 22:04:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->foreign('current_historic_supplier_product_id')->references('id')->on('historic_supplier_products');
        });
    }


    public function down()
    {
        // Hack to remove the foreign constraint, because laravel stuff not work
        DB::statement('alter table central.supplier_products drop constraint supplier_products_current_historic_supplier_product_id_foreign');
        // Schema::table('supplier_products', function (Blueprint $table) {
        //  $table->dropForeign('current_historic_supplier_product_id');
        //});
    }
};
