<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:39:34 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('family_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('family_id')->index();
            $table->foreign('family_id')->references('id')->on('families');
            $table->unsignedInteger('number_products')->default(0);

            $productStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
            foreach ($productStates as $productState) {
                $table->unsignedInteger('number_products_state_'.str_replace('-', '_', $productState))->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('family_stats');
    }
};
