<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 29 Nov 2023 21:56:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasInventoryStats
{
    use HasLocationsStats;
    public function inventoryStats(Blueprint $table): Blueprint
    {

        $table->unsignedSmallInteger('number_warehouses')->default(0);
        $table->unsignedSmallInteger('number_warehouse_areas')->default(0);
        $table = $this->locationsStats($table);

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
