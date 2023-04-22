<?php

use App\Enums\Sales\Order\OrderStateEnum;
use App\Enums\Sales\Order\OrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');


            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media');

            $table->unsignedInteger('customer_client_id')->nullable()->index();
            $table->foreign('customer_client_id')->references('id')->on('customer_clients');

            $table->string('number')->nullable()->index();
            $table->string('customer_number')->index()->nullable()->comment('Customers own order number');


            $table->string('state')->default(OrderStateEnum::CREATING->value)->index();
            $table->string('status')->default(OrderStatusEnum::PROCESSING->value)->index();


            $table->dateTimeTz('date');

            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('in_warehouse_at')->nullable();
            $table->dateTimeTz('packed_at')->nullable();
            $table->dateTimeTz('finalised_at')->nullable();
            $table->dateTimeTz('dispatched_at')->nullable();

            $table->dateTimeTz('cancelled_at')->nullable();


            $table->boolean('is_invoiced')->default('false');
            $table->boolean('is_picking_on_hold')->nullable();
            $table->boolean('can_dispatch')->nullable();

            $table->decimal('items_discounts', 16)->default(0);
            $table->decimal('items_net', 16)->default(0);

            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('public.currencies');
            $table->decimal('exchange', 16, 6)->default(1);

            $table->decimal('charges', 16)->default(0);
            $table->decimal('shipping', 16)->default(null)->nullable();
            $table->decimal('net', 16)->default(0);
            $table->decimal('tax', 16)->default(0);

            $table->jsonb('data');

            $table->timestampsTz();
            $table->softDeletesTz();


            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
