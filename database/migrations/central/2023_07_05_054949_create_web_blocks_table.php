<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jul 2023 15:48:50 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('web_blocks', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->index();
            $table->string('scope')->index();
            $table->string('code');
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('web_block_type_id');
            $table->foreign('web_block_type_id')->references('id')->on('web_block_types')->onUpdate('cascade')->onDelete('cascade');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['web_block_type_id','code']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('web_blocks');
    }
};
