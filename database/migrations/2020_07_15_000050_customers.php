<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class Customers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores');
            $table->string('slug')->index();
            $table->string('name')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('mobile')->nullable()->index();
            $table->string('status')->index();
            $table->string('state')->index();
            $table->string('country_id')->nullable()->index();

            $table->unsignedMediumInteger('billing_address_id')->nullable()->index();
            $table->foreign('billing_address_id')->references('id')->on('addresses');
            $table->unsignedMediumInteger('delivery_address_id')->nullable()->index();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');

            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        });

        Schema::create('customer_clients', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->string('slug')->index();
            $table->string('code')->nullable()->index();
            $table->string('name')->nullable();
            $table->unsignedMediumInteger('delivery_address_id')->nullable()->index();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');
            $table->json('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        });

        Schema::create('customer_portfolio', function (Blueprint $table) {
            $table->mediumIncrements('id');

            $table->unsignedMediumInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedMediumInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products');
            $table->string('code')->nullable()->index();
            $table->json('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
            $table->unique(['customer_id','product_id']);
        });

        Schema::create('customer_portfolio_timeline', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('customer_portfolio_id')->index();
            $table->foreign('customer_portfolio_id')->references('id')->on('customer_portfolio');
            $table->string('action')->index();
            $table->dateTimeTz('date', 0);
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        });



        Schema::create(
            'baskets', function (Blueprint $table) {
            $table->id();

            $table->morphs('parent');

            $table->boolean('status')->default(false);

            $table->unsignedMediumInteger('delivery_id')->nullable()->index();
            $table->foreign('delivery_id')->references('id')->on('addresses');

            $table->unsignedMediumInteger('items')->default(0);

            $table->decimal('items_discounts', 16, 2)->default(0);
            $table->decimal('items_net', 16, 2)->default(0);

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



            $table->string('transaction_type')->index();
            $table->unsignedBigInteger('transaction_id')->nullable();


            $table->decimal('quantity', 16, 3);

            $table->decimal('discounts', 16, 2)->default(0);
            $table->decimal('net', 16, 2)->default(0);

            $table->unsignedMediumInteger('tax_band_id')->nullable()->index();
            $table->foreign('tax_band_id')->references('id')->on('tax_bands');

            $table->jsonb('data');

            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();

            $table->index(['transaction_id','transaction_type']);
            $table->unique(
                [
                    'transaction_type',
                    'legacy_id'
                ]
            );

        }
        );



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('basket_transactions');
        Schema::dropIfExists('tax_bands');

        Schema::dropIfExists('baskets');
        Schema::dropIfExists('customer_portfolio_timeline');
        Schema::dropIfExists('customer_portfolio');
        Schema::dropIfExists('customer_clients');
        Schema::dropIfExists('customers');


    }
}
