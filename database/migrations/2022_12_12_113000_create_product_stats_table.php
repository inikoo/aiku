<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 21:14:16 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Stubs\Migrations\HasSalesIntervals;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesIntervals;

    public function up(): void
    {
        Schema::create('product_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedInteger('number_historic_assets')->default(0);
            foreach (ProductStateEnum::cases() as $case) {
                $table->unsignedInteger('number_products_state_'.$case->snake())->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_stats');
    }
};
