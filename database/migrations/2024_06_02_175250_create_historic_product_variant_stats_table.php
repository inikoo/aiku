<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 19:53:00 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('historic_product_variant_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('historic_product_variant_id')->index();
            $table->foreign('historic_product_variant_id')->references('id')->on('historic_product_variants');
            $table->timestampsTz();
        });


    }



    public function down(): void
    {
        Schema::dropIfExists('historic_product_variant_stats');
    }
};
