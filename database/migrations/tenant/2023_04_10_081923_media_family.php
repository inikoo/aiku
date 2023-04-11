<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 10 Apr 2023 10:21:48 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('media_family', function (Blueprint $table) {
            $table->unsignedInteger('family_id')->index();
            $table->string('type')->index();
            $table->foreign('family_id')->references('id')->on('stock_families');
            $table->unsignedBigInteger('media_id')->index();
            $table->foreign('media_id')->references('id')->on('media');
            $table->unique(['family_id', 'media_id']);
            $table->string('owner_type')->index();
            $table->unsignedInteger('owner_id');
            $table->boolean('public')->default(false)->index();

            $table->timestampsTz();
            $table->index(['owner_type', 'owner_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('media_family');
    }
};
