<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shopify_user_has_clients', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('shopify_user_id');
            $table->foreign('shopify_user_id')->references('id')->on('shopify_users')->onDelete('cascade');

            $table->unsignedBigInteger('customer_client_id');
            $table->foreign('customer_client_id')->references('id')->on('customer_clients')->onDelete('cascade');
            $table->unsignedBigInteger('shopify_customer_client_id');

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shopify_user_has_clients');
    }
};
