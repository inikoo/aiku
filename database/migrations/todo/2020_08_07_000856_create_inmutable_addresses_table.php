<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateInmutableAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inmutable_addresses', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores');
            $table->string('slug');
            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->index(['tenant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inmutable_addresses');
    }
}
