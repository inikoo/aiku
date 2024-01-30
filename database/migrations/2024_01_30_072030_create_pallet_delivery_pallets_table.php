<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('pallet_delivery_pallets', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('pallet_delivery_id')->index();
            $table->foreign('pallet_delivery_id')->references('id')->on('pallet_deliveries')->onDelete('cascade');

            $table->unsignedSmallInteger('pallet_id')->index();
            $table->foreign('pallet_id')->references('id')->on('pallets')->onDelete('cascade');

            $table->unsignedSmallInteger('items_quantity')->default(0);

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('pallet_delivery_pallets');
    }
};
