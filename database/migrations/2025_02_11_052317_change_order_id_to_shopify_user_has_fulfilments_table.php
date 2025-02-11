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
            $table->dropForeign(['order_id']);
            $table->renameColumn('order_id', 'model_id');
            $table->string('model_type')->after('model_id');
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
            $table->renameColumn('model_id', 'order_id');
            $table->dropColumn('model_type');
            $table->foreign('order_id')->on('orders')->references('id');
        });
    }
};
