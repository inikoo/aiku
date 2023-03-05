<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 18:49:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('stock_trade_unit', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->foreign('stock_id')->references('id')->on('stocks');
            $table->unsignedBigInteger('trade_unit_id')->nullable();
            $table->foreign('trade_unit_id')->references('id')->on('trade_units');
            $table->decimal('quantity', 12, 3);

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('stock_trade_unit');
    }
};
