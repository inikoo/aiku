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
            $table->unsignedBigInteger('shopify_user_id')->change();
            $table->unsignedBigInteger('model_id')->change();
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
            $table->smallInteger('shopify_user_id')->change();
            $table->smallInteger('model_id')->change();
        });
    }
};
