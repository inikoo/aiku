<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Apr 2023 13:47:40 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Inventory\Stock\StockQuantityStatusEnum;
use App\Enums\Inventory\Stock\StockStateEnum;
use App\Enums\Inventory\StockFamily\StockFamilyStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tenant_inventory_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedSmallInteger('number_warehouses')->default(0);

            $table->unsignedSmallInteger('number_warehouse_areas')->default(0);

            $table->unsignedInteger('number_locations')->default(0);
            $table->unsignedInteger('number_locations_state_operational')->default(0);
            $table->unsignedInteger('number_locations_state_broken')->default(0);
            $table->unsignedSmallInteger('number_empty_locations')->default(0);


            $table->unsignedBigInteger('number_stock_families')->default(0);

            foreach (StockFamilyStateEnum::cases() as $stockFamilyState) {
                $table->unsignedBigInteger('number_stock_families_state_'.$stockFamilyState->snake())->default(0);
            }

            $table->unsignedBigInteger('number_stocks')->default(0);
            foreach (StockStateEnum::cases() as $stockState) {
                $table->unsignedBigInteger('number_stocks_state_'.$stockState->snake())->default(0);
            }
            foreach (StockQuantityStatusEnum::cases() as $stockQuantityStatus) {
                $table->unsignedBigInteger('number_stocks_quantity_status_'.$stockQuantityStatus->snake())->default(0);
            }


            $table->unsignedBigInteger('number_deliveries')->default(0);
            $table->unsignedBigInteger('number_deliveries_type_order')->default(0);
            $table->unsignedBigInteger('number_deliveries_type_replacement')->default(0);

            $deliveryStates = [
                'submitted',
                'picker-assigned',
                'picking',
                'picked',
                'packing',
                'packed',
                'finalised',
                'dispatched',
            ];

            foreach ($deliveryStates as $deliveryState) {
                $table->unsignedBigInteger('number_deliveries_state_'.str_replace('-', '_', $deliveryState))->default(0);
            }

            foreach ($deliveryStates as $deliveryState) {
                $table->unsignedBigInteger('number_deliveries_cancelled_at_state_'.str_replace('-', '_', $deliveryState))->default(0);
            }


            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tenant_inventory_stats');
    }
};
