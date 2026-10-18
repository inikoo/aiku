<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('seo_content_briefs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            $table->string('keyword', 255);
            $table->string('country_code', 2);
            $table->string('language_code', 8);
            $table->string('state', 16)->default('pending');
            $table->unsignedSmallInteger('requested_by_user_id')->nullable();
            $table->foreign('requested_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->jsonb('pages')->nullable();
            $table->jsonb('questions')->nullable();
            $table->jsonb('related_searches')->nullable();
            $table->jsonb('stats')->nullable();
            $table->jsonb('our_page')->nullable();
            $table->jsonb('brief')->nullable();
            $table->string('model', 64)->nullable();
            $table->text('error')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->timestampsTz();
            $table->index(['shop_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_content_briefs');
    }
};
