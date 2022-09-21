<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('admins');

            $table->string('username')->unique();
            $table->string('password');


            $table->boolean('status')->default(true)->index();
            $table->unsignedSmallInteger('language_id');
            $table->foreign('language_id')->references('id')->on('central.languages');
            $table->unsignedSmallInteger('timezone_id');
            $table->foreign('timezone_id')->references('id')->on('central.timezones');

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
