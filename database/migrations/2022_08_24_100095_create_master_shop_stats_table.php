<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 25 Dec 2024 02:36:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Stubs\Migrations\HasCatalogueStats;
use App\Stubs\Migrations\HasHelpersStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasHelpersStats;
    use HasCatalogueStats;
    public function up(): void
    {
        Schema::create('master_shop_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('master_shop_id')->index();
            $table->foreign('master_shop_id')->references('id')->on('master_shops');

            $table = $this->shopsStatsFields($table);


            // Department
            $table->unsignedInteger('number_master_departments')->default(0);
            $table->unsignedInteger('number_current_master_departments')->default(0);
            foreach (ProductCategoryStateEnum::cases() as $departmentState) {
                $table->unsignedInteger('number_master_departments_state_'.$departmentState->snake())->default(0);
            }

            // Sub Department
            $table->unsignedInteger('number_master_sub_departments')->default(0);
            $table->unsignedSmallInteger('number_current_master_sub_departments')->default(0)->comment('state: active+discontinuing');


            // Families
            $table->unsignedInteger('number_master_families')->default(0);
            $table->unsignedSmallInteger('number_current_master_families')->default(0)->comment('state: active+discontinuing');
            $table->unsignedInteger('number_orphan_master_families')->default(0);

            // Product
            $table->unsignedInteger('number_master_products')->default(0);
            $table->unsignedInteger('number_current_master_products')->default(0)->comment('state: active+discontinuing');


            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_shop_stats');
    }
};
