<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 22 Nov 2024 10:47:22 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasBackInStockReminderStats;
use App\Stubs\Migrations\HasCatalogueStats;
use App\Stubs\Migrations\HasFavouritesStats;
use App\Stubs\Migrations\HasSalesIntervals;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesIntervals;
    use HasCatalogueStats;
    use HasFavouritesStats;
    use HasBackInStockReminderStats;

    public function up(): void
    {
        Schema::create('product_sales_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products');




            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_sales_stats');
    }
};
