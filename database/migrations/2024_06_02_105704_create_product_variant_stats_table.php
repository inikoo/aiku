<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 13:00:58 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_variant_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_variant_id')->index();
            $table->foreign('product_variant_id')->references('id')->on('product_variants');

            $table->unsignedInteger('number_historic_product_variants')->default(0);


            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_variant_stats');
    }
};
