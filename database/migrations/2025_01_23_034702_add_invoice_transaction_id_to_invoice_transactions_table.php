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
        Schema::table('invoice_transactions', function (Blueprint $table) {
            $table->unsignedInteger('invoice_transaction_id')->nullable()->index();
            $table->foreign('invoice_transaction_id')->references('id')->on('invoice_transactions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_transactions', function (Blueprint $table) {
            $table->dropColumn('invoice_transaction_id');
        });
    }
};
