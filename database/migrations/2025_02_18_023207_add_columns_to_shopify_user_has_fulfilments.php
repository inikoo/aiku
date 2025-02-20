<?php

use App\Enums\Dropshipping\ShopifyFulfilmentStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shopify_user_has_fulfilments', function (Blueprint $table) {
            $table->string('state')->default(ShopifyFulfilmentStateEnum::OPEN->value);
        });
    }


    public function down(): void
    {
        Schema::table('shopify_user_has_fulfilments', function (Blueprint $table) {
            $table->dropColumn(['state']);
        });
    }
};
