<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:36:25 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Market\Family\FamilyStateEnum;
use App\Enums\Market\Product\ProductStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_category_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('product_category_id')->index();
            $table->foreign('product_category_id')->references('id')->on('product_categories');

            $table->unsignedSmallInteger('number_sub_product_categories')->default(0);

            $table->unsignedSmallInteger('number_families')->default(0);
            foreach (FamilyStateEnum::cases() as $familyState) {
                $table->unsignedSmallInteger('number_families_state_'.$familyState->snake())->default(0);
            }

            $table->unsignedInteger('number_products')->default(0);
            foreach (ProductStateEnum::cases() as $productState) {
                $table->unsignedInteger('number_products_state_'.$productState->snake())->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_category_stats');
    }
};
