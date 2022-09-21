<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 05:28:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('product_trade_unit', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('trade_unit_id')->nullable();
            $table->foreign('trade_unit_id')->references('id')->on('trade_units');
            $table->decimal('quantity', 12, 3);
            $table->string('notes')->nullable();

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('product_trade_unit');
    }
};
