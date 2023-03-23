<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:36:25 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Marketing\Family\FamilyStateEnum;
use App\Enums\Marketing\Product\ProductStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('department_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('department_id')->index();
            $table->foreign('department_id')->references('id')->on('departments');

            $table->unsignedSmallInteger('number_sub_departments')->default(0);

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


    public function down()
    {
        Schema::dropIfExists('department_stats');
    }
};
