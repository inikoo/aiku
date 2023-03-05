<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 21:58:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
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
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->dropForeign('current_historic_supplier_product_id');
        });
    }
};
