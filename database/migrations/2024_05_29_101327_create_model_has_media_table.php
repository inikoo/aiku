<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 08:37:52 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('model_has_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('organisation_id')->index()->nullable();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->string('scope')->nullable()->index();
            $table->unsignedInteger('media_id');
            $table->foreign('media_id')->references('id')->on('media');
            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->timestampsTz();
            $table->index(['model_type','model_id']);
            $table->unique(['media_id','model_type','model_id','scope']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('model_has_media');
    }
};
