<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('customer_poll_option_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_poll_id')->index();
            $table->foreign('customer_poll_id')->references('id')->on('customer_polls');
            $table->unsignedInteger('customer_poll_option_id')->index();
            $table->foreign('customer_poll_option_id')->references('id')->on('customer_poll_options');
            $table->integer('number_customers')->default(0);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('customer_poll_option_stats');
    }
};