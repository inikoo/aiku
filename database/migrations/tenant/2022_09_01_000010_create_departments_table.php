<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:35:32 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->index();
            $table->string('code')->index();

            $table->unsignedMediumInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->enum('state', ['creating', 'active', 'suspended', 'discontinuing', 'discontinued'])->nullable()->index();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->jsonb('data');


            $table->timestampstz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('departments');
    }
};
