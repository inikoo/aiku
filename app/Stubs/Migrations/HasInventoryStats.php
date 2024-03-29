<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 29 Nov 2023 21:56:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Dispatch\DeliveryNoteItem\DeliveryNoteItemStateEnum;
use App\Enums\Inventory\OrgStock\OrgStockQuantityStatusEnum;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Enums\Inventory\OrgStockFamily\OrgStockFamilyStateEnum;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Enums\SupplyChain\StockFamily\StockFamilyStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasInventoryStats
{
    use HasLocationsStats;

    public function warehousesStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_warehouses')->default(0);
        $table->unsignedSmallInteger('number_warehouse_areas')->default(0);

        return $this->locationsStats($table);
    }


    public function stocksStats(Blueprint $table): Blueprint
    {


        $table->unsignedInteger('number_stock_families')->default(0);

        foreach (StockFamilyStateEnum::cases() as $stockFamilyState) {
            $table->unsignedInteger('number_stock_families_state_'.$stockFamilyState->snake())->default(0);
        }

        $table->unsignedInteger('number_stocks')->default(0);
        foreach (StockStateEnum::cases() as $stockState) {
            $table->unsignedInteger('number_stocks_state_'.$stockState->snake())->default(0);
        }

        return $table;
    }

    public function orgStocksStats(Blueprint $table): Blueprint
    {


        $table->unsignedInteger('number_stock_families')->default(0);

        foreach (OrgStockFamilyStateEnum::cases() as $stockFamilyState) {
            $table->unsignedInteger('number_org_stock_families_state_'.$stockFamilyState->snake())->default(0);
        }

        $table->unsignedInteger('number_stocks')->default(0);
        foreach (OrgStockStateEnum::cases() as $stockState) {
            $table->unsignedInteger('number_org_stocks_state_'.$stockState->snake())->default(0);
        }
        foreach (OrgStockQuantityStatusEnum::cases() as $stockQuantityStatus) {
            $table->unsignedInteger('number_org_stocks_quantity_status_'.$stockQuantityStatus->snake())->default(0);
        }

        return $table;
    }

    public function deliveryNoteStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_deliveries')->default(0);
        $table->unsignedInteger('number_deliveries_type_order')->default(0);
        $table->unsignedInteger('number_deliveries_type_replacement')->default(0);



        foreach (DeliveryNoteItemStateEnum::cases() as $case) {
            $table->unsignedInteger('number_deliveries_state_'.$case->snake())->default(0);
        }
        foreach (DeliveryNoteItemStateEnum::cases() as $case) {
            $table->unsignedInteger('number_deliveries_cancelled_at_state_'.$case->snake())->default(0);
        }


        return $table;
    }

}
