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
            $table->string('no_fulfilment_reason')->nullable();
            $table->string('no_fulfilment_reason_notes')->nullable();
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
            $table->dropColumn(['no_fulfilment_reason', 'no_fulfilment_reason_notes']);
        });
    }
};
