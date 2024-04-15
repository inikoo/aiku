<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->after('id', function () use ($table) {
                $table->unsignedInteger('org_payment_service_provider_id')->index()->nullable();
                $table->foreign('org_payment_service_provider_id')->references('id')->on('org_payment_service_providers');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_accounts', function (Blueprint $table) {
            //
        });
    }
};
