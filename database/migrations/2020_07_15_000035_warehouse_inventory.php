<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 18:35:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WarehouseInventory extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create(
            'warehouses', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->index();
            $table->string('name');
            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
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
            'warehouse_areas', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->string('slug')->index();
            $table->string('name');
            $table->json('data');
            $table->timestampsTz();
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
            $table->unsignedSmallInteger('tenant_id');

        }
        );

        Schema::create(
            'locations', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedMediumInteger('warehouse_id')->index();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->unsignedMediumInteger('warehouse_area_id')->nullable()->index();
            $table->foreign('warehouse_area_id')->references('id')->on('warehouse_areas');

            $table->string('slug')->index();
            $table->string('code')->index();

            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);

            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
            $table->unsignedSmallInteger('tenant_id');
            $table->string('natural_order_code')->nullable()->index();

        }
        );

        Schema::create(
            'stocks', function (Blueprint $table) {
            $table->increments('id');

            $table->string('state')->nullable()->index();
            $table->string('quantity_status')->nullable()->index();

            $table->string('sellable')->default('yes')->index();
            $table->string('raw_material')->default('no')->index();


            $table->string('slug')->index();

            $table->string('code')->index();
            $table->string('barcode')->index()->nullable();

            $table->text('description')->nullable();

            $table->unsignedMediumInteger('packed_in')->default(1);
            $table->unsignedMediumInteger('stored_in')->nullable();

            $table->string('unit_type')->default('piece')->index()->nullable;
            $table->decimal('unit_quantity', 16, 3)->nullable();
            $table->float('available_forecast')->nullable()->comment('days');


            $table->decimal('value', 16, 3)->nullable();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('images');
            $table->unsignedBigInteger('package_image_id')->nullable();
            $table->foreign('package_image_id')->references('id')->on('images');
            $table->jsonb('settings');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
        }
        );

        Schema::create(
            'location_stock', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('stock_id');
            $table->foreign('stock_id')->references('id')->on('stocks');
            $table->unsignedMediumInteger('location_id');
            $table->foreign('location_id')->references('id')->on('locations');

            $table->decimal('quantity', 16, 3);
            $table->smallInteger('picking_priority')->default(0)->index();
            $table->jsonb('data');
            $table->jsonb('settings');

            $table->dateTimeTz('audited_at')->nullable()->index();
            $table->timestampsTz();
            $table->unique(['stock_id','location_id','tenant_id']);
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_location_id')->nullable();
            $table->unsignedMediumInteger('legacy_stock_id')->nullable();
            $table->unique(['legacy_location_id','legacy_stock_id']);

        }
        );


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::dropIfExists('location_stock');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('warehouse_areas');
        Schema::dropIfExists('warehouses');

    }
}
