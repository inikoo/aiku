<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 May 2023 15:26:45 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_product_trade_unit', function (Blueprint $table) {
            $table->unsignedInteger('supplier_product_id')->nullable();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->unsignedInteger('trade_unit_id')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->string('notes')->nullable();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('supplier_product_trade_unit');
    }
};
