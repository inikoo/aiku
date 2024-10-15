<?php

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Stubs\Migrations\HasHelpersStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasHelpersStats;
    public function up(): void
    {
        Schema::create('master_shop_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('master_shop_id')->index();
            $table->foreign('master_shop_id')->references('id')->on('master_shops');

            // Department
            $table->unsignedInteger('number_departments')->default(0);
            $table->unsignedInteger('number_current_master_departments')->default(0);
            foreach (ProductCategoryStateEnum::cases() as $departmentState) {
                $table->unsignedInteger('number_master_departments_state_'.$departmentState->snake())->default(0);
            }

            // Sub Department
            $table->unsignedInteger('number_master_sub_departments')->default(0);
            $table->unsignedSmallInteger('number_current_master_sub_departments')->default(0)->comment('state: active+discontinuing');
            foreach (ProductCategoryStateEnum::cases() as $familyState) {
                $table->unsignedInteger('number_master_sub_departments_state_'.$familyState->snake())->default(0);
            }

            // Families
            $table->unsignedInteger('number_master_families')->default(0);
            $table->unsignedSmallInteger('number_current_master_families')->default(0)->comment('state: active+discontinuing');
            foreach (ProductCategoryStateEnum::cases() as $familyState) {
                $table->unsignedInteger('number_master_families_state_'.$familyState->snake())->default(0);
            }
            $table->unsignedInteger('number_orphan_master_families')->default(0);

            // Product
            $table->unsignedInteger('number_master_products')->default(0);
            $table->unsignedInteger('number_current_master_products')->default(0)->comment('state: active+discontinuing');
            foreach (ProductStateEnum::cases() as $productState) {
                $table->unsignedInteger('number_master_products_state_'.$productState->snake())->default(0);
            }

            $table = $this->uploadStats($table);

            $table->softDeletesTz();

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_shop_stats');
    }
};
