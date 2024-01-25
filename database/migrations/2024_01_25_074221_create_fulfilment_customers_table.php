<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('fulfilment_customers', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedInteger('fulfilment_id');
            $table->foreign('fulfilment_id')->references('id')->on('fulfilments');

            $table->smallInteger('number_pallets')->default(0);
            $table->smallInteger('number_stored_items')->default(0);

            $table->jsonb('data');

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('fulfilment_customers');
    }
};
