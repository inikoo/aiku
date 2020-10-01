<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 02 Sep 2020 02:27:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('code',2)->unique()->index();
            $table->string('code_iso3',3)->nullable()->index();
            $table->unsignedSmallInteger('code_iso_numeric')->nullable()->index();
            $table->unsignedInteger('geoname_id')->nullable()->index();

            $table->string('phone_code')->nullable();
            $table->string('currency_code')->nullable();

            $table->string('name');
            $table->string('continent');
            $table->string('capital');
            $table->string('timezone')->comment('Timezone in capital');

            $table->unsignedSmallInteger('shippers_count')->default(0);
            $table->jsonb('data');
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
        Schema::dropIfExists('countries');

    }
}
