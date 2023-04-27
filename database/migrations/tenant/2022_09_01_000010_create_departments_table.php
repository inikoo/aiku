<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:35:32 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique();
            $table->string('code')->index();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media');

            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');

            $table->morphs('parent');
            $table->string('type')->index();
            $table->boolean('is_family')->default(false);

            $table->string('state')->nullable()->index();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->jsonb('data');


            $table->timestampstz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
