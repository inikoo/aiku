<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('supplier_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_id')->index();
            $table->string('provider_type');
            $table->string('number');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unique(['provider_id', 'provider_type']);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('supplier_deliveries');
    }
};
