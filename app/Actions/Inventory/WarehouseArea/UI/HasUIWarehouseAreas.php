<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Models\Central\Tenant;
use App\Models\Inventory\Warehouse;

trait HasUIWarehouseAreas
{
    public function getBreadcrumbs(string $routeName, Warehouse|Tenant $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('areas')
                    ]
                ],
            ];
        };


        return match ($routeName) {
            'inventory.warehouse_areas.index' =>
            array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb()
            ),
            'inventory.warehouses.show.warehouse_areas.index' =>
            array_merge(
                (new ShowWarehouse())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }
}
