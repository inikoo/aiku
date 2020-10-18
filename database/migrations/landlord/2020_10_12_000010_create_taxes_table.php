<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 18 Oct 2020 18:34:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'taxes', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->boolean('status');
            $table->string('slug');
            $table->string('name');
            $table->string('country_code');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taxes');


    }
}
