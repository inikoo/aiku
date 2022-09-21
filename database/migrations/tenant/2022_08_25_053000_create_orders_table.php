<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedMediumInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');


            $table->unsignedBigInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedBigInteger('customer_client_id')->nullable()->index();
            $table->foreign('customer_client_id')->references('id')->on('customers');

            $table->string('number')->nullable()->index();

            $table->enum('state', ['in-basket', 'in-process', 'in-warehouse', 'packed', 'packed-done', 'dispatched', 'returned', 'cancelled'])->default('in-basket')->index();


            $table->unsignedMediumInteger('billing_address_id')->nullable()->index();
            $table->foreign('billing_address_id')->references('id')->on('addresses');

            $table->unsignedMediumInteger('delivery_address_id')->nullable()->index();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');

            $table->unsignedBigInteger('items')->default(0)->comment('number of items');

            $table->decimal('items_discounts', 16)->default(0);
            $table->decimal('items_net', 16)->default(0);

            $table->unsignedSmallInteger('currency_id');
            $table->decimal('exchange', 16, 6)->default(1);

            $table->decimal('charges', 16)->default(0);
            $table->decimal('shipping', 16)->default(null)->nullable();
            $table->decimal('net', 16)->default(0);
            $table->decimal('tax', 16)->default(0);

            $table->jsonb('data');

            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique()->index();
        });
    }


    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
