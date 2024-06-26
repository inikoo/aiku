<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('pallet_return_services', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('pallet_return_id');
            $table->unsignedSmallInteger('service_id');
            $table->integer('quantity');

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('pallet_return_services');
    }
};
