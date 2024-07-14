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
        Schema::create('web_block_types', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('web_block_type_category_id');
            $table->foreign('web_block_type_category_id')->references('id')->on('web_block_type_categories')->onUpdate('cascade')->onDelete('cascade');

            $table->string('slug')->unique()->index();
            $table->string('scope')->index();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->jsonb('blueprint');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unique(['web_block_type_category_id', 'code']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('web_block_types');
    }
};
