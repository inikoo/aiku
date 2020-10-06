<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Stores extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create(
            'stores', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('state')->index();

            $table->string('slug');
            $table->string('name');
            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedMediumInteger('legacy_id')->nullable();
            $table->unsignedSmallInteger('tenant_id');

            $table->index(
                [
                    'tenant_id',
                    'slug'
                ]
            );
        }
        );

        Schema::create(
            'products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->nullable()->index();

            $table->unsignedMediumInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('stores');


            $table->string('state')->nullable()->index();
            $table->boolean('status')->nullable()->index();

            $table->string('code')->index();
            $table->text('name')->nullable();
            $table->text('description')->nullable();

            $table->decimal('unit_price');
            $table->unsignedSmallInteger('units');

            $table->unsignedMediumInteger('available')->default(0)->nullable();

            $table->jsonb('settings');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');

            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        }
        );

        Schema::create(
            'product_historic_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedMediumInteger('product_id')->nullable()->index();
            $table->foreign('product_id')->references('id')->on('products');
            $table->timestampTz('date', 0)->nullable();

            $table->decimal('unit_price');
            $table->unsignedSmallInteger('units');

            $table->jsonb('data');
            $table->timestampsTz();

            $table->unsignedMediumInteger('legacy_id')->nullable()->index();


        }
        );


        Schema::table(
            'products', function (Blueprint $table) {

            $table->unsignedMediumInteger('product_historic_variation_id')->nullable()->index()->after('store_id');
            $table->foreign('product_historic_variation_id')->references('id')->on('product_historic_variations');

        }
        );

        Schema::create(
            'product_stock', function (Blueprint $table) {
            $table->increments('id');


            $table->unsignedMediumInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedMediumInteger('stock_id')->index();
            $table->foreign('stock_id')->references('id')->on('stocks');

            $table->decimal('ratio', 12, 4)->default(1);


            $table->unsignedMediumInteger('available')->default(0)->nullable();

            $table->jsonb('data');
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
        Schema::dropIfExists('product_stock');
        Schema::table(
            'products', function (Blueprint $table) {
            $table->dropColumn('product_historic_variation_id');
        }
        );
        Schema::dropIfExists('product_historic_variations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('stores');
    }
}

