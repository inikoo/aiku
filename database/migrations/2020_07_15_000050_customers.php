<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class Customers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores');
            $table->string('slug')->index();
            $table->string('name')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('mobile')->nullable()->index();
            $table->string('status')->index();
            $table->string('state')->index();
            $table->string('country_id')->nullable()->index();

            $table->unsignedMediumInteger('billing_address_id')->nullable()->index();
            $table->foreign('billing_address_id')->references('id')->on('addresses');
            $table->unsignedMediumInteger('delivery_address_id')->nullable()->index();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');

            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
