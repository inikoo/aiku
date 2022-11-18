<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:36:20 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {


    public function up()
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('slug')->unique();
            $table->unsignedMediumInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->enum('state', ['construction', 'live', 'maintenance', 'closed'])->default('construction')->index();
            $table->string('code')->index();
            $table->string('domain')->unique();
            $table->string('name');
            $table->jsonb('settings');
            $table->jsonb('data');
            $table->jsonb('webnodes');
            $table->timestampsTz();
            $table->timestampTz('launched_at')->nullable();
            $table->timestampTz('closed_at')->nullable();
            $table->unsignedBigInteger('current_layout_id')->index()->nullable();

            $table->softDeletesTz();

            $table->unsignedBigInteger('source_id')->nullable()->unique();


        });
    }


    public function down()
    {
        Schema::dropIfExists('websites');
    }
};
