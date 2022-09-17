<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 22:41:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()

    {
        Schema::create('organisation_inventory_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();


            $table->unsignedSmallInteger('number_warehouses')->default(0);

            $table->unsignedSmallInteger('number_warehouse_areas')->default(0);

            $table->unsignedMediumInteger('number_locations')->default(0);
            $table->unsignedMediumInteger('number_locations_state_operational')->default(0);
            $table->unsignedMediumInteger('number_locations_state_broken')->default(0);
            $table->unsignedSmallInteger('number_empty_locations')->default(0);


            $table->unsignedBigInteger('number_stocks')->default(0);
            $stockStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
            foreach ($stockStates as $stockState) {
                $table->unsignedBigInteger('number_stocks_state_'.str_replace('-', '_', $stockState))->default(0);
            }
            $stockQuantityStatuses = ['surplus', 'optimal', 'low', 'critical', 'out-of-stock', 'error'];
            foreach ($stockQuantityStatuses as $stockQuantityStatus) {
                $table->unsignedBigInteger('number_stocks_quantity_status_'.str_replace('-', '_', $stockQuantityStatus))->default(0);
            }


            $table->unsignedBigInteger('number_deliveries')->default(0);
            $table->unsignedBigInteger('number_deliveries_type_order')->default(0);
            $table->unsignedBigInteger('number_deliveries_type_replacement')->default(0);

            $deliveryStates = [
                'ready-to-be-picked',
                'picker-assigned',
                'picking',
                'picked',
                'packing',
                'packed',
                'packed-done',
                'approved',
                'dispatched',
                'cancelled',
                'cancelled-to-restock',
            ];

            foreach ($deliveryStates as $deliveryState) {
                $table->unsignedBigInteger('number_deliveries_state_'.str_replace('-', '_', $deliveryState))->default(0);
            }


            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('organisation_inventory_stats');
    }
};
