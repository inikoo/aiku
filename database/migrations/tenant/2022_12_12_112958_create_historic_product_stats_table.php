<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 19:35:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('historic_product_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('historic_product_id')->index();
            $table->foreign('historic_product_id')->references('id')->on('historic_products');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('historic_product_stats');
    }
};
