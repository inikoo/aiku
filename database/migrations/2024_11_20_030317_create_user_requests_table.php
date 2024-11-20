<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTimeTz('date');
            $table->string('route_name');
            $table->jsonb('route_params');
            $table->string('section');
            $table->string('os');
            $table->string('device');
            $table->string('browser');
            $table->string('ip_address');
            $table->jsonb('location');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('user_requests');
    }
};
