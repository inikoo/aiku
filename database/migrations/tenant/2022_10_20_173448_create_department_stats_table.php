<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:36:25 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('department_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('department_id')->index();
            $table->foreign('department_id')->references('id')->on('departments');


            $table->unsignedBigInteger('number_families')->default(0);
            $familyStates = ['creating', 'active', 'suspended', 'discontinuing', 'discontinued'];
            foreach ($familyStates as $familyState) {
                $table->unsignedBigInteger('number_families_state_'.str_replace('-', '_', $familyState))->default(0);
            }

            $table->unsignedBigInteger('number_products')->default(0);
            $productStates = ['creating', 'active', 'suspended', 'discontinuing', 'discontinued'];
            foreach ($productStates as $productState) {
                $table->unsignedBigInteger('number_products_state_'.str_replace('-', '_', $productState))->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('department_stats');
    }
};
