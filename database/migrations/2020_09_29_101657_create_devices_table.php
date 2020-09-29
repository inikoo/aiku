<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->string('app',255)->index();

            $table->string('uid',255)->index();
            $table->string('tag',1000)->index();


            $table->unsignedMediumInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedMediumInteger('personal_access_token_id')->nullable()->index();
            $table->foreign('personal_access_token_id')->references('id')->on('personal_access_tokens');


            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
