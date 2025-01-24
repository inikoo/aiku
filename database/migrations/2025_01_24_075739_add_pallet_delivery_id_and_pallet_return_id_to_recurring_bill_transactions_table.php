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
        Schema::table('recurring_bill_transactions', function (Blueprint $table) {
            $table->unsignedInteger('pallet_delivery_id')->index()->nullable();
            $table->foreign('pallet_delivery_id')->references('id')->on('pallet_deliveries');
            $table->unsignedInteger('pallet_return_id')->index()->nullable();
            $table->foreign('pallet_return_id')->references('id')->on('pallet_returns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recurring_bill_transactions', function (Blueprint $table) {
            $table->dropColumn('pallet_delivery_id');
            $table->dropColumn('pallet_return_id');
        });
    }
};
