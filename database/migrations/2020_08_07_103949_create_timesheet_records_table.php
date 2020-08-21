<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 22 Aug 2020 00:20:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimesheetRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timesheet_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('timesheet_id');
            $table->foreign('timesheet_id')->references('id')->on('timesheets');
            $table->unsignedMediumInteger('clocking_nfc_tag_id')->nullable()->index();
            $table->foreign('clocking_nfc_tag_id')->references('id')->on('clocking_nfc_tags');
            $table->dateTimeTz('date')->index();
            $table->string('state');
            $table->json('data');
            $table->unsignedMediumInteger('legacy_id')->nullable();
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
        Schema::dropIfExists('timesheet_records');
    }
}
