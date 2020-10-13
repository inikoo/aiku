<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 12 Oct 2020 20:27:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('original_images', function (Blueprint $table) {
            $table->id();
            $table->string('checksum',32)->index()->unique();

            $table->unsignedBigInteger('filesize')->index();
            $table->double('megapixels')->index();

            $table->binary('image_data');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
        });

        Schema::create('processed_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_images_id');
            $table->foreign('original_images_id')->references('id')->on('original_images');

            $table->string('checksum',32)->index()->unique();

            $table->unsignedBigInteger('filesize')->index();
            $table->double('megapixels')->index();

            $table->binary('image_data');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
        });

        Schema::create('communal_images', function (Blueprint $table) {
            $table->id();
            $table->morphs('imageable');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unique(['imageable_type','imageable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processed_images');
        Schema::dropIfExists('original_images');
        Schema::dropIfExists('communal_images');

    }
}
