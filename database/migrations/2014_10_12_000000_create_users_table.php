<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique()->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('facebook_id')->nullable()->index();
            $table->string('twitter_id')->nullable()->index();
            $table->string('google_id')->nullable()->index();
            $table->rememberToken();
            $table->unsignedSmallInteger('number_organisations')->default(0);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('users');
    }
};
