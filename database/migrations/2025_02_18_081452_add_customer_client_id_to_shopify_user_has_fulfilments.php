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
        Schema::table('shopify_user_has_fulfilments', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_client_id')->nullable();
            $table->foreign('customer_client_id')->references('id')->on('customer_clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_user_has_fulfilments', function (Blueprint $table) {
            $table->dropForeign(['customer_client_id']);
            $table->dropColumn(['customer_client_id']);
        });
    }
};
