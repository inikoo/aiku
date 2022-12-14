<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 20:47:14 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('current_historic_product_id')->references('id')->on('historic_products');
        });
    }


    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('current_historic_product_id');
        });
    }
};
