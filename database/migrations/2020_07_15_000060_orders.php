<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 13:57:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class Orders extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create(
            'shippers', function (Blueprint $table) {
            $table->smallIncrements('id');



            $table->string('status')->index();
            $table->string('slug');
            $table->string('code');

            $table->jsonb('settings');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->unsignedSmallInteger('tenant_id');
        }
        );



        Schema::create(
            'charges', function (Blueprint $table) {
            $table->mediumIncrements('id');


            $table->unsignedMediumInteger('store_id')->nullable()->index();
            $table->foreign('store_id')->references('id')->on('stores');

            $table->boolean('status');

            $table->string('type')->index();
            $table->string('slug');
            $table->string('name');

            $table->jsonb('settings');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->unsignedSmallInteger('tenant_id');
        }
        );

        Schema::create(
            'shipping_schemas', function (Blueprint $table) {
            $table->mediumIncrements('id');

            $table->unsignedMediumInteger('store_id')->nullable()->index();
            $table->foreign('store_id')->references('id')->on('stores');

            $table->boolean('status')->default(true)->index();

            $table->string('slug');
            $table->string('name');

            $table->jsonb('data');
            $table->jsonb('settings');

            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->unsignedSmallInteger('tenant_id');
        }
        );

        Schema::create(
            'shipping_zones', function (Blueprint $table) {
            $table->mediumIncrements('id');

            $table->unsignedMediumInteger('shipping_schema_id')->nullable()->index();
            $table->foreign('shipping_schema_id')->references('id')->on('shipping_schemas');
            $table->boolean('status')->default(true)->index();



            $table->smallInteger('precedence')->default(0);
            $table->string('slug');
            $table->string('code');
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->unsignedSmallInteger('tenant_id');
        }
        );

        Schema::create(
            'tax_codes', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug');
            $table->string('name');
            $table->decimal('rate', 10, 4);


            $table->unsignedMediumInteger('country_id')->nullable()->index();
            $table->foreign('country_id')->references('id')->on('countries');


            $table->jsonb('data');
            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        }
        );

        Schema::create(
            'orders', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores');
            $table->unsignedMediumInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedMediumInteger('customer_client_id')->nullable()->index();
            $table->foreign('customer_client_id')->references('id')->on('customer_clients');


            $table->unsignedMediumInteger('billing_id')->nullable()->index();
            $table->foreign('billing_id')->references('id')->on('addresses');
            $table->unsignedMediumInteger('delivery_id')->nullable()->index();
            $table->foreign('delivery_id')->references('id')->on('addresses');


            $table->string('number')->index();
            $table->string('state')->nullable()->index();
            $table->string('status')->index();

            $table->string('payment_status')->nullable()->index();


            $table->decimal('items_gross', 16, 2)->default(0);
            $table->decimal('items_discounts', 16, 2)->default(0);
            $table->decimal('charges', 16, 2)->default(0);
            $table->decimal('shipping', 16, 2)->default(null)->nullable();
            $table->decimal('net', 16, 2)->default(0);
            $table->decimal('tax', 16, 2)->default(0);


            $table->decimal('payment', 16, 2)->default(0);

            $table->decimal('weight', 16, 2)->nullable()->default(0);
            $table->unsignedMediumInteger('items')->default(0);

            $table->dateTimeTz('date', 0)->index();
            $table->dateTimeTz('submitted_at', 0)->nullable();
            $table->dateTimeTz('warehoused_at', 0)->nullable();
            $table->dateTimeTz('picking_at', 0)->nullable();
            $table->dateTimeTz('packed_at', 0)->nullable();
            $table->dateTimeTz('invoiced_at', 0)->nullable();
            $table->dateTimeTz('dispatched_at', 0)->nullable();
            $table->dateTimeTz('cancelled_at', 0)->nullable();

            $table->jsonb('data');
            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        }
        );

        Schema::create(
            'delivery_notes', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('number')->index();
            $table->string('type')->default('purchase')->index()->comment('purchase|donation|samplereplacement');

            $table->string('state')->nullable()->index();
            $table->string('status')->nullable()->index();

            //$table->unsignedMediumInteger('store_id')->index();
            //$table->foreign('store_id')->references('id')->on('stores');

            //$table->unsignedMediumInteger('customer_id')->index();
            //$table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedMediumInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders');

            $table->unsignedMediumInteger('delivery_address_id')->nullable()->index();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');
            $table->unsignedMediumInteger('shipper_id')->nullable()->index();
            $table->foreign('shipper_id')->references('id')->on('shippers');

            $table->decimal('weight', 16, 2)->nullable()->default(0);
            $table->unsignedMediumInteger('number_stocks')->default(0);
            $table->unsignedMediumInteger('number_picks')->default(0);


            $table->unsignedMediumInteger('picker_id')->nullable()->index()->comment('Main picker');
            $table->foreign('picker_id')->references('id')->on('employees');
            $table->unsignedMediumInteger('packer_id')->nullable()->index()->comment('Main packer');
            $table->foreign('packer_id')->references('id')->on('employees');

            $table->dateTimeTz('date', 0)->index();

            $table->dateTimeTz('order_submitted_at', 0)->nullable();

            $table->dateTimeTz('assigned_at', 0)->nullable();
            $table->dateTimeTz('picking_at', 0)->nullable();
            $table->dateTimeTz('picked_at', 0)->nullable();
            $table->dateTimeTz('packing_at', 0)->nullable();
            $table->dateTimeTz('packed_at', 0)->nullable();
            $table->dateTimeTz('dispatched_at', 0)->nullable();
            $table->dateTimeTz('cancelled_at', 0)->nullable();


            $table->json('data');
            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable();
        }
        );

        Schema::create(
            'returns', function (Blueprint $table) {

            $table->mediumIncrements('id');
            $table->string('number')->unique()->index();
            $table->string('state')->nullable()->index();
            $table->string('status')->nullable()->index();

            $table->unsignedMediumInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores');

            $table->unsignedMediumInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedMediumInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders');

            $table->unsignedMediumInteger('delivery_note_id')->nullable()->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');

            $table->decimal('weight', 16, 2)->default(0);
            $table->unsignedMediumInteger('items')->default(0);

            $table->dateTimeTz('date', 0)->index();

            $table->dateTimeTz('assigned_at', 0)->nullable();
            $table->dateTimeTz('placing_at', 0)->nullable();
            $table->dateTimeTz('placed_at', 0)->nullable();

            $table->dateTimeTz('cancelled_at', 0)->nullable();


            $table->json('data');
            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable();
        }
        );


        Schema::create(
            'invoices', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores');
            $table->unsignedMediumInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedMediumInteger('order_id')->nullable()->index();
            $table->foreign('order_id')->references('id')->on('orders');

            $table->unsignedMediumInteger('billing_id')->nullable()->index();
            $table->foreign('billing_id')->references('id')->on('addresses');
            $table->unsignedMediumInteger('delivery_id')->nullable()->index();
            $table->foreign('delivery_id')->references('id')->on('addresses');


            $table->string('number')->unique()->index();
            $table->string('type')->nullable()->index();
            $table->string('payment_status')->nullable()->index();

            $table->decimal('net', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('payment', 16, 2)->default(0);


            $table->dateTimeTz('date', 0)->index();
            $table->dateTimeTz('paid_at', 0)->nullable();


            $table->jsonb('data');
            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        }
        );


        Schema::create(
            'order_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores');
            $table->unsignedMediumInteger('order_id')->nullable()->index();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->unsignedMediumInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->morphs('orderable');


            //$table->unsignedMediumInteger('product_id')->index();
            //$table->foreign('product_id')->references('id')->on('products');
            //$table->unsignedMediumInteger('product_historic_variation_id')->index();
            //$table->foreign('product_historic_variation_id')->references('id')->on('product_historic_variations');


            $table->decimal('quantity', 16, 3);
            $table->decimal('discounts', 16, 2)->default(0);
            $table->decimal('net', 16, 2)->default(0);

            $table->unsignedMediumInteger('tax_code_id')->nullable()->index();
            $table->foreign('tax_code_id')->references('id')->on('tax_codes');

            $table->jsonb('data');

            $table->timestampsTz();

            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();


        }
        );

        Schema::create(
            'invoice_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('invoice_id')->nullable()->index();

            $table->unsignedMediumInteger('order_transactions_id')->nullable()->index();
            $table->foreign('order_transactions_id')->references('id')->on('order_transactions');

            $table->morphs('invoiceable');


            $table->decimal('quantity', 16, 3);
            $table->decimal('net', 16, 2)->default(0);
            $table->decimal('tax', 16, 2)->default(0);

            $table->jsonb('data');

            $table->timestampsTz();

            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        }
        );


        Schema::create(
            'stock_movements', function (Blueprint $table) {
            $table->id();


            $table->unsignedMediumInteger('stock_id')->nullable()->index();
            $table->foreign('stock_id')->references('id')->on('stocks');

            $table->unsignedMediumInteger('location_id')->nullable()->index();
            $table->foreign('location_id')->references('id')->on('locations');

            $table->decimal('quantity', 16, 3);

            $table->jsonb('data');

            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();

        }
        );

        Schema::create(
            'pickings', function (Blueprint $table) {
            $table->id();


            $table->string('state')->index()->default('created')->comment('created|assigned|picking|queried|waiting|picked|packing|done');
            $table->string('status')->index()->default('created')->comment('processing|packed|partially_packed|out_of_stock|cancelled');


            $table->unsignedMediumInteger('delivery_note_id')->nullable()->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');


            $table->unsignedMediumInteger('stock_id')->nullable()->index();
            $table->foreign('stock_id')->references('id')->on('stocks');

            $table->unsignedMediumInteger('picked_by')->nullable()->index();
            $table->foreign('picked_by')->references('id')->on('employees');


            $table->decimal('required', 16, 3);
            $table->decimal('picked', 16, 3)->nullable();
            $table->decimal('weight', 16, 3)->nullable();

            $table->jsonb('data');


            $table->dateTimeTz('assigned_at', 0)->nullable();

            $table->dateTimeTz('picking_at', 0)->nullable();
            $table->dateTimeTz('picked_at', 0)->nullable();
            $table->dateTimeTz('packing_at', 0)->nullable();
            $table->dateTimeTz('packed_at', 0)->nullable();


            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();

        }
        );

        Schema::create(
            'delivery_note_items', function (Blueprint $table) {
            $table->id();


            $table->boolean('was_dispatched')->index();
            $table->string('status')->index()->default('created')->comment('dispatched|partially_dispatched|out_of_stock|cancelled');


            $table->unsignedMediumInteger('delivery_note_id')->nullable()->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');

            $table->unsignedMediumInteger('stock_id')->nullable()->index();
            $table->foreign('stock_id')->references('id')->on('stocks');


            $table->unsignedMediumInteger('picked_by')->nullable()->index();
            $table->foreign('picked_by')->references('id')->on('employees');
            $table->unsignedMediumInteger('packed_by')->nullable()->index();
            $table->foreign('packed_by')->references('id')->on('employees');

            $table->decimal('required', 16, 3);

            $table->decimal('dispatched', 16, 3);
            $table->decimal('weight', 16, 3)->nullable();

            $table->jsonb('data');


            $table->dateTimeTz('ordered_at', 0)->nullable();
            $table->dateTimeTz('warehoused_at', 0)->nullable();

            $table->dateTimeTz('assigned_at', 0)->nullable();
            $table->dateTimeTz('picking_at', 0)->nullable();
            $table->dateTimeTz('picked_at', 0)->nullable();
            $table->dateTimeTz('packing_at', 0)->nullable();
            $table->dateTimeTz('packed_at', 0)->nullable();
            $table->dateTimeTz('dispatched_at', 0)->nullable();

            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();

        }
        );


        Schema::create(
            'baskets', function (Blueprint $table) {
            $table->id();

            $table->morphs('parent');

            $table->boolean('status')->default(false);

            $table->unsignedMediumInteger('delivery_id')->nullable()->index();
            $table->foreign('delivery_id')->references('id')->on('addresses');

            $table->unsignedMediumInteger('items')->default(0);

            $table->decimal('items_gross', 16, 2)->default(0);
            $table->decimal('items_discounts', 16, 2)->default(0);
            $table->decimal('charges', 16, 2)->default(0);
            $table->decimal('shipping', 16, 2)->default(null)->nullable();
            $table->decimal('net', 16, 2)->default(0);
            $table->decimal('tax', 16, 2)->default(0);

            $table->jsonb('data');

            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();

            $table->unique(
                [
                    'parent_type',
                    'parent_id'
                ]
            );

        }
        );


        Schema::create(
            'basket_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('basket_id')->index();
            $table->foreign('basket_id')->references('id')->on('baskets');

            $table->morphs('transaction');

            $table->decimal('quantity', 16, 3);
            $table->decimal('gross', 16, 2)->default(0);
            $table->decimal('discounts', 16, 2)->default(0);
            $table->decimal('net', 16, 2)->default(0);

            $table->decimal('tax', 16, 2)->default(0);

            $table->jsonb('data');

            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();

            $table->unique(
                [
                    'transaction_type',
                    'legacy_id'
                ]
            );

        }
        );


        Schema::create(
            'placing_returns', function (Blueprint $table) {
            $table->id();

            $table->unsignedMediumInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedMediumInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedMediumInteger('delivery_note_id')->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');


            $table->unsignedMediumInteger('product_historic_variation_id')->index();
            $table->foreign('product_historic_variation_id')->references('id')->on('product_historic_variations');


            $table->decimal('quantity', 16, 3);


            $table->jsonb('data');

            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        }
        );


        Schema::create(
            'delivery_note_picker', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('delivery_note_id')->nullable()->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');
            $table->unsignedMediumInteger('picker_id')->nullable()->index();
            $table->foreign('picker_id')->references('id')->on('employees');
            $table->decimal('weight', 16, 2)->nullable()->default(0);
            $table->unsignedMediumInteger('number_stocks')->default(0);
            $table->unsignedMediumInteger('number_picks')->default(0);

            $table->timestampsTz();
        }
        );

        Schema::create(
            'delivery_note_packer', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('delivery_note_id')->nullable()->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');
            $table->unsignedMediumInteger('packer_id')->nullable()->index();
            $table->foreign('packer_id')->references('id')->on('employees');
            $table->unsignedMediumInteger('number_stocks')->default(0);
            $table->unsignedMediumInteger('number_picks')->default(0);

            $table->timestampsTz();
        }
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('delivery_note_packer');
        Schema::dropIfExists('delivery_note_picker');

        Schema::dropIfExists('placing_returns');

        Schema::dropIfExists('basket_transactions');
        Schema::dropIfExists('baskets');

        Schema::dropIfExists('delivery_note_items');

        Schema::dropIfExists('pickings');
        Schema::dropIfExists('stock_movements');


        Schema::dropIfExists('invoice_transactions');

        Schema::dropIfExists('order_transactions');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('returns');

        Schema::dropIfExists('delivery_notes');

        Schema::dropIfExists('orders');
        Schema::dropIfExists('tax_codes');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('shipping_schemas');
        Schema::dropIfExists('charges');
        Schema::dropIfExists('shippers');

    }
}
