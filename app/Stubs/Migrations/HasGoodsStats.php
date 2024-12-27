<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 21 Dec 2024 15:20:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Goods\Stock\StockStateEnum;
use App\Enums\Goods\StockFamily\StockFamilyStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasGoodsStats
{
    public function goodsStatsFields(Blueprint $table): Blueprint
    {
        $table = $this->masterShopsStatsFields($table);
        $table = $this->stockTradeUnitsFields($table);
        $table = $this->stockFamiliesStatsFields($table);
        $table = $this->stockStatsFields($table);

        return $this->ingredientsFields($table);
    }

    public function masterShopsStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_master_shops')->default(0);
        $table->unsignedInteger('number_current_master_shops')->default(0)->comment('status=true');

        return $table;
    }

    public function stockTradeUnitsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_trade_units')->default(0);
        $table->unsignedInteger('number_trade_units_with_barcode')->default(0);
        $table->unsignedInteger('number_trade_units_with_net_weight')->default(0);
        $table->unsignedInteger('number_trade_units_with_gross_weight')->default(0);
        $table->unsignedInteger('number_trade_units_with_marketing_weight')->default(0);
        $table->unsignedInteger('number_trade_units_with_dimensions')->default(0);
        $table->unsignedInteger('number_trade_units_with_images')->default(0);
        return $table;
    }

    public function ingredientsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_ingredients')->default(0);

        return $table;
    }

    public function stockFamiliesStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_stock_families')->default(0);
        $table->unsignedInteger('number_current_stock_families')->default(0)->comment('active + discontinuing');

        foreach (StockFamilyStateEnum::cases() as $stockFamilyState) {
            $table->unsignedInteger('number_stock_families_state_'.$stockFamilyState->snake())->default(0);
        }

        return $table;
    }

    public function stockStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_stocks')->default(0);
        $table->unsignedInteger('number_current_stocks')->default(0)->comment('active + discontinuing');

        foreach (StockStateEnum::cases() as $stockState) {
            $table->unsignedInteger('number_stocks_state_'.$stockState->snake())->default(0);
        }

        return $table;
    }


}
