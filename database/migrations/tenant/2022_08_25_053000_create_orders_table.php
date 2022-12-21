\<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedMediumInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');


            $table->unsignedBigInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedBigInteger('customer_client_id')->nullable()->index();
            $table->foreign('customer_client_id')->references('id')->on('customer_clients');

            $table->string('number')->nullable()->index();
            $table->string('customer_number')->index()->nullable()->comment('Customers own order number');

            $table->enum('type',['b2b','b2c','dropshipping'])->index()->nullable();
            $table->enum('state', ['submitted', 'in-warehouse','packed', 'finalised', 'dispatched'])->default('in-basket')->index();

            $table->boolean('is_invoiced')->default('false');
            $table->boolean('is_picking_on_hold')->nullable();
            $table->boolean('can_dispatch')->nullable();


            $table->unsignedMediumInteger('billing_address_id')->nullable()->index();
            $table->foreign('billing_address_id')->references('id')->on('addresses');

            $table->unsignedMediumInteger('delivery_address_id')->nullable()->index();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');



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
            $table->dateTimeTz('cancelled_at')->nullable()->comment('equivalent deleted_at');


            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
