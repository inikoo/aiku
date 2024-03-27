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
        Schema::table('pallets', function (Blueprint $table) {
            $table->unsignedSmallInteger('proforma_id')->nullable()->index()->after('pallet_return_id');
            $table->foreign('proforma_id')->references('id')
                ->on('fulfilment_proformas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pallets', function (Blueprint $table) {
            //
        });
    }
};
