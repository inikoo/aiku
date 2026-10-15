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
        Schema::create('google_tag_manager_containers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->string('account_id', 32);
            $table->string('account_name');
            $table->string('container_id', 32);
            $table->string('name');
            $table->string('public_id', 32)->index();
            $table->string('usage_context', 64)->nullable();
            $table->jsonb('domain_names')->nullable();
            $table->string('tag_manager_url', 512)->nullable();
            $table->timestampTz('fetched_at')->nullable();
            $table->timestampsTz();
            $table->unique(['group_id', 'account_id', 'container_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_tag_manager_containers');
    }
};
