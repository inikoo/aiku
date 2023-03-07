<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('historic_rents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rent_id')->index();
            $table->foreign('rent_id')->references('id')->on('rents');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('historic_rents');
    }
};
