<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addressables', function (Blueprint $table) {
            $table->bigIncrements('address_id');
            $table->foreignId('addressable_id')->index();
            $table->string('addressable_type')->index();
            $table->timestampsTz();
        });

        Schema::table(
            'addresses', function (Blueprint $table) {
            $table->string('checksum')->index()->change();
            $table->foreignId('owner_id')->nullable()->index();
            $table->string('owner_type')->nullable()->index();
            $table->unsignedSmallInteger('country_id')->nullable()->index();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->index(['checksum', 'owner_id','owner_type']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addressables');
        Schema::table(
            'addresses', function (Blueprint $table) {
            $table->dropColumn('owner_id');
            $table->dropColumn('owner_type');
            $table->dropColumn('country_id');
            }
        );
    }
}
