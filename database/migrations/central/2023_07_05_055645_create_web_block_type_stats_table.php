<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:15:57 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('web_block_type_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('web_block_type_id');
            $table->foreign('web_block_type_id')->references('id')->on('web_block_types')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('number_tenants')->default(0);
            $table->integer('number_web_blocks')->default(0);
            $table->integer('number_websites')->default(0);
            $table->integer('number_webpages')->default(0);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('web_block_type_stats');
    }
};
