<?php

use App\Enums\Accounting\PaymentAccountShop\PaymentAccountShopStateEnum;
use App\Models\Accounting\PaymentAccountShop;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_account_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_pas')->default(0);
            foreach (PaymentAccountShopStateEnum::cases() as $state) {
                $table->unsignedInteger("number_pas_state_{$state->snake()}")->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_account_stats', function (Blueprint $table) {
            //
        });
    }
};
