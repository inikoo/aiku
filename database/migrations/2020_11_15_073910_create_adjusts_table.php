<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 15 Nov 2020 15:45:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdjustsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adjusts', function (Blueprint $table) {
            $table->mediumIncrements('id');

            $table->unsignedMediumInteger('store_id')->nullable()->index();
            $table->foreign('store_id')->references('id')->on('stores');


            $table->string('type')->index()->nullable();
            $table->string('name');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unique(['store_id','type']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adjusts');
    }
}
