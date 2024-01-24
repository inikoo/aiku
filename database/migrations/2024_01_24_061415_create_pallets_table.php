<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('pallets', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('reference')->index()->collation('und_ci');

            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedInteger('location_id')->index()->nullable();
            $table->foreign('location_id')->references('id')->on('locations');

            $table->string('notes');

            $table->dateTimeTz('received_at')->nullable();
            $table->dateTimeTz('booked_in_at')->nullable();
            $table->dateTimeTz('settled_at')->nullable();
            $table->jsonb('data');

            $table->softDeletes();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('pallets');
    }
};
