<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('dropshipping_customer_portfolio_stats', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('dropshipping_customer_portfolio_id')->index();
            $table->foreign('dropshipping_customer_portfolio_id')->references('id')->on('dropshipping_customer_portfolios');

            $table->unsignedSmallInteger('amount')->default(0);
            $table->unsignedSmallInteger('number_orders')->default(0);
            $table->unsignedSmallInteger('number_ordered_quantity')->default(0);
            $table->unsignedSmallInteger('number_clients')->default(0);
            $table->unsignedSmallInteger('last_ordered_at')->nullable();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('dropshipping_customer_portfolio_stats');
    }
};
