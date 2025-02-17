<?php

use App\Stubs\Migrations\HasPaymentStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasPaymentStats;
    public function up(): void
    {
        Schema::create('payment_account_shop_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payment_account_shop_id')->index();
            $table->foreign('payment_account_shop_id')->references('id')->on('payment_account_shop');
            $table = $this->paymentStats($table);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('payment_account_shop_stats');
    }
};
