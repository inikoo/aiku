<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 29 Nov 2023 21:56:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Inventory\OrgStock\OrgStockQuantityStatusEnum;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Enums\SupplyChain\StockFamily\StockFamilyStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasInventoryStats
{
    use HasLocationsStats;
    public function inventoryStats(Blueprint $table): Blueprint
    {

        $table->unsignedSmallInteger('number_warehouses')->default(0);
        $table->unsignedSmallInteger('number_warehouse_areas')->default(0);
        $table = $this->locationsStats($table);

        $table->unsignedInteger('number_stock_families')->default(0);

        foreach (StockFamilyStateEnum::cases() as $stockFamilyState) {
            $table->unsignedInteger('number_stock_families_state_'.$stockFamilyState->snake())->default(0);
        }

        $table->unsignedInteger('number_stocks')->default(0);
        foreach (OrgStockStateEnum::cases() as $stockState) {
            $table->unsignedInteger('number_stocks_state_'.$stockState->snake())->default(0);
        }
        foreach (OrgStockQuantityStatusEnum::cases() as $stockQuantityStatus) {
            $table->unsignedInteger('number_stocks_quantity_status_'.$stockQuantityStatus->snake())->default(0);
        }

        $table->unsignedInteger('number_deliveries')->default(0);
        $table->unsignedInteger('number_deliveries_type_order')->default(0);
        $table->unsignedInteger('number_deliveries_type_replacement')->default(0);

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
            $table->unsignedInteger('number_deliveries_state_'.str_replace('-', '_', $deliveryState))->default(0);
        }

        foreach ($deliveryStates as $deliveryState) {
            $table->unsignedInteger('number_deliveries_cancelled_at_state_'.str_replace('-', '_', $deliveryState))->default(0);
        }

        return $table;
    }
}
