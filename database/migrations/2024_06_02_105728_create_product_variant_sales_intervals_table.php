<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 13:00:10 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasSalesIntervals;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesIntervals;
    public function up(): void
    {
        Schema::create('product_variant_sales_intervals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_variant_id')->index();
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
            $table=$this->salesIntervalFields($table, ['shop_amount', 'org_amount', 'group_amount']);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_variant_sales_intervals');
    }
};
