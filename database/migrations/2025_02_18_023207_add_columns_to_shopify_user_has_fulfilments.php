<?php

use App\Enums\Dropshipping\ShopifyFulfilmentStateEnum;
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
            $table->string('state')->default(ShopifyFulfilmentStateEnum::OPEN->value);
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
            $table->dropColumn(['state']);
        });
    }
};
