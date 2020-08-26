<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 27 Aug 2020 00:01:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpGeolocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ip_geolocations', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip');
            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('region_code',64)->nullable();
            $table->string('region_name')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('town')->nullable();
            $table->enum('status',['InProcess','OK','Error'])->default('InProcess');
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
        Schema::dropIfExists('ip_geolocations');
    }
}
