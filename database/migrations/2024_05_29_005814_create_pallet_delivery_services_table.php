<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('pallet_delivery_services', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('pallet_delivery_id');
            $table->unsignedSmallInteger('service_id');
            $table->integer('quantity');

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('pallet_delivery_services');
    }
};
