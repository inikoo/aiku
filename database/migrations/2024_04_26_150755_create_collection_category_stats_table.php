<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Apr 2024 16:16:14 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
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
        Schema::create('collection_category_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedInteger('collection_category_id')->index();
            $table->foreign('collection_category_id')->references('id')->on('collection_categories');
            $table=$this->catalogueHeadStats($table);
            $table=$this->salesStats($table, ['shop_amount','org_amount','group_amount']);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('collection_category_stats');
    }
};
