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
        Schema::table('pallet_deliveries', function (Blueprint $table) {
            $table->unsignedInteger('invoice_id')->nullable()->index();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();

            $table->unsignedInteger('recurring_bill_id')->index()->nullable();
            $table->foreign('recurring_bill_id')->references('id')->on('recurring_bills')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pallet_deliveries', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
            $table->dropColumn('recurring_bill_id');
        });
    }
};
