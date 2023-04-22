<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->morphs('userable');
            $table->string('username')->unique();
            $table->string('password');


            $table->boolean('status')->default(true)->index();
            $table->unsignedSmallInteger('language_id');
            $table->foreign('language_id')->references('id')->on('public.languages');
            $table->unsignedSmallInteger('timezone_id');
            $table->foreign('timezone_id')->references('id')->on('public.timezones');
            $table->string('email')->nullable()->unique();

            $table->jsonb('data');
            $table->jsonb('settings');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->rememberToken();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('admin_users');
    }
};
