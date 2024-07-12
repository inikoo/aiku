<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 24 Mar 2024 22:31:10 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Ordering\Order\OrderHandingTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderStatusEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedInteger('customer_client_id')->nullable()->index();
            $table->foreign('customer_client_id')->references('id')->on('customer_clients');

            $table->string('number')->nullable()->index();
            $table->string('customer_number')->index()->nullable()->comment('Customers own order number');


            $table->string('state')->default(OrderStateEnum::CREATING->value)->index();
            $table->string('status')->default(OrderStatusEnum::PROCESSING->value)->index();
            $table->string('handing_type')->default(OrderHandingTypeEnum::SHIPPING->value)->index();

            $table->boolean('customer_locked')->default(false);
            $table->boolean('billing_locked')->default(false);
            $table->boolean('delivery_locked')->default(false);


            $table->unsignedInteger('billing_address_id')->index()->nullable();
            $table->foreign('billing_address_id')->references('id')->on('addresses');
            $table->unsignedInteger('delivery_address_id')->index()->nullable();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');
            $table->unsignedInteger('collection_address_id')->index()->nullable();
            $table->foreign('collection_address_id')->references('id')->on('addresses');


            $table->unsignedSmallInteger('billing_country_id')->index()->nullable();
            $table->foreign('billing_country_id')->references('id')->on('countries');
            $table->unsignedSmallInteger('delivery_country_id')->index()->nullable();
            $table->foreign('delivery_country_id')->references('id')->on('countries');

            $table->dateTimeTz('date');

            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('in_warehouse_at')->nullable();
            $table->dateTimeTz('handling_at')->nullable();
            $table->dateTimeTz('packed_at')->nullable();
            $table->dateTimeTz('finalised_at')->nullable();
            $table->dateTimeTz('dispatched_at')->nullable();

            $table->dateTimeTz('cancelled_at')->nullable();

            $table->dateTimeTz('settled_at')->nullable()->comment('dispatched_at|cancelled_at');


            $table->boolean('is_invoiced')->default('false');
            $table->boolean('is_picking_on_hold')->nullable();
            $table->boolean('can_dispatch')->nullable();

            $table->decimal('items_discounts', 16)->default(0);
            $table->decimal('items_net', 16)->default(0);

            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->decimal('grp_exchange', 16, 4)->default(1);
            $table->decimal('org_exchange', 16, 4)->default(1);

            $table->decimal('charges', 16)->default(0);
            $table->decimal('shipping', 16)->default(null)->nullable();
            $table->decimal('net', 16)->default(0);
            $table->decimal('tax', 16)->default(0);

            $table->jsonb('data');

            $table->timestampsTz();
            $table->softDeletesTz();


            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
