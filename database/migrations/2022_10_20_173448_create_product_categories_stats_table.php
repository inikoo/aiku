<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:36:25 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Stubs\Migrations\HasCatalogueStats;
use App\Stubs\Migrations\HasSalesStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasCatalogueStats;
    use HasSalesStats;

    public function up(): void
    {
        Schema::create('product_category_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('product_category_id')->index();
            $table->foreign('product_category_id')->references('id')->on('product_categories');

            $table=$this->catalogueHeadStats($table);
            $table=$this->salesStats($table, ['shop_amount','org_amount','group_amount']);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_category_stats');
    }
};
