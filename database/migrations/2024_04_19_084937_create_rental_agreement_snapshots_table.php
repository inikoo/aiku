<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('rental_agreement_snapshots', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('rental_agreement_id')->index();
            $table->foreign('rental_agreement_id')->references('id')->on('rental_agreements');
            $table->jsonb('data');
            $table->dateTimeTz('date');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('rental_agreement_snapshots');
    }
};
