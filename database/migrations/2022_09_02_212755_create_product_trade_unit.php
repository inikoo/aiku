<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 05:28:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_trade_unit', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedInteger('trade_unit_id')->nullable();
            $table->decimal('units_per_main_product', 12, 3);
            $table->string('notes')->nullable();

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_trade_unit');
    }
};
