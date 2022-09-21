<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 18:31:50 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('trade_units', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable()->index();
            $table->string('code')->index();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('barcode')->index()->nullable();
            $table->float('weight')->nullable();
            $table->jsonb('dimensions')->nullable();

            $table->string('type')->default('piece')->index()->nullable()->comment('unit type');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unsignedBigInteger('source_id')->nullable()->unique()->index();
        });
    }


    public function down()
    {
        Schema::dropIfExists('trade_units');
    }
};
