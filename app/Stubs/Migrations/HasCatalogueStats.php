<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Jul 2023 12:33:47 Malaysia Time, plane Bali -> KL
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\ProductCategory\ProductCategoryStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasCatalogueStats
{
    public function catalogueStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_departments')->default(0);
        $table->unsignedInteger('number_current_departments')->default(0);

        foreach (ProductCategoryStateEnum::cases() as $departmentState) {
            $table->unsignedInteger('number_departments_state_'.$departmentState->snake())->default(0);
        }

        return $this->catalogueHeadStats($table);
    }

    public function catalogueHeadStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_sub_departments')->default(0);
        $table->unsignedSmallInteger('number_current_sub_departments')->default(0)->comment('state: active+discontinuing');
        foreach (ProductCategoryStateEnum::cases() as $familyState) {
            $table->unsignedInteger('number_sub_departments_state_'.$familyState->snake())->default(0);
        }

        $table->unsignedInteger('number_families')->default(0);
        $table->unsignedSmallInteger('number_current_families')->default(0)->comment('state: active+discontinuing');
        foreach (ProductCategoryStateEnum::cases() as $familyState) {
            $table->unsignedInteger('number_families_state_'.$familyState->snake())->default(0);
        }
        $table->unsignedInteger('number_orphan_families')->default(0);

        $table->unsignedInteger('number_products')->default(0);
        $table->unsignedInteger('number_current_products')->default(0)->comment('state: active+discontinuing');

        foreach (ProductStateEnum::cases() as $productState) {
            $table->unsignedInteger('number_products_state_'.$productState->snake())->default(0);
        }

        return $table;
    }
}
