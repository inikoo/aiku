<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\UI\Inventory\InventoryDashboard;
use App\Models\Inventory\Warehouse;

trait HasUIWarehouse
{
    public function getBreadcrumbs(Warehouse $warehouse): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                'inventory.warehouses.show' => [
                    'route'           => 'inventory.warehouses.show',
                    'routeParameters' => $warehouse->slug,
                    'name'            => $warehouse->code,
                    'index'           => [
                        'route'   => 'inventory.warehouses.index',
                        'overlay' => __('warehouses list')
                    ],
                    'modelLabel'      => [
                        'label' => __('warehouse')
                    ],
                ],
            ]
        );
    }
}
