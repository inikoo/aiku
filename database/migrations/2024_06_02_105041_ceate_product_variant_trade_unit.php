<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 12:50:51 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_variant_trade_unit', function (Blueprint $table) {
            $table->unsignedInteger('product_variant_id')->nullable();
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
            $table->unsignedInteger('trade_unit_id')->nullable();
            $table->decimal('units', 12, 3);
            $table->string('notes')->nullable();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_variant_trade_unit');

    }
};
