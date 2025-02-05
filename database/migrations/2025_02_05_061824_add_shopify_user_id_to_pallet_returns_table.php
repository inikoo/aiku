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
        Schema::table('pallet_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('shopify_user_id')->nullable();
            $table->foreign('shopify_user_id')->references('id')->on('shopify_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pallet_returns', function (Blueprint $table) {
            $table->dropColumn('shopify_user_id');
        });
    }
};
