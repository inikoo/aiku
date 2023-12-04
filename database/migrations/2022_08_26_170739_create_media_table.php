<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 May 2023 13:48:51 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('organisation_id')->index()->nullable();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('model_type')->nullable();
            $table->unsignedInteger('model_id')->nullable();
            $table->uuid()->nullable()->unique();
            $table->string('collection_name')->index();
            $table->string('scope')->nullable()->index();
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->string('checksum')->index()->nullable();
            $table->boolean('is_animated')->default(false);
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
            $table->index(['model_type','model_id']);
            $table->index(['collection_name','scope']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
