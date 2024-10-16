<?php

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Stubs\Migrations\HasCatalogueStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_product_category_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('master_product_category_id')->index();
            $table->foreign('master_product_category_id')->references('id')->on('master_product_categories');
            $table->unsignedInteger('number_sub_departments')->default(0);

            $table->unsignedSmallInteger('number_current_master_sub_departments')->default(0)->comment('state: active+discontinuing');
            foreach (ProductCategoryStateEnum::cases() as $familyState) {
                $table->unsignedInteger('number_master_sub_departments_state_'.$familyState->snake())->default(0);
            }
    
            $table->unsignedInteger('number_master_families')->default(0);
            $table->unsignedSmallInteger('number_current_master_families')->default(0)->comment('state: active+discontinuing');
            foreach (ProductCategoryStateEnum::cases() as $familyState) {
                $table->unsignedInteger('number_master_families_state_'.$familyState->snake())->default(0);
            }
            $table->unsignedInteger('number_orphan_master_families')->default(0);

            $table->unsignedInteger('number_master_products')->default(0);
            $table->unsignedInteger('number_current_master_products')->default(0)->comment('state: active+discontinuing');
    
            foreach (ProductStateEnum::cases() as $productState) {
                $table->unsignedInteger('number_master_products_state_'.$productState->snake())->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_product_category_stats');
    }
};
