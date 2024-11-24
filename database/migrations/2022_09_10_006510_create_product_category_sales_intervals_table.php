<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Apr 2024 16:17:51 British Summer Time, Sheffield, UK
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
        Schema::create('product_category_sales_intervals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_category_id')->index();
            $table->foreign('product_category_id')->references('id')->on('product_categories');
            $table = $this->dateIntervals($table, [
                'sales',
                'sales_org_currency',
                'sales_grp_currency'
            ]);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_category_sales_intervals');
    }
};