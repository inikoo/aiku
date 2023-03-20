<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Models\Central\Tenant;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;

trait HasUILocations
{
    public function getBreadcrumbs(string $routeName, WarehouseArea|Warehouse|Tenant|null $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('locations')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'inventory.locations.index' => array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb()
            ),
            'inventory.warehouses.show.locations.index' => array_merge(
                (new ShowWarehouse())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            'inventory.warehouse-areas.show.locations.index' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs('inventory.warehouse-areas.show', $parent),
                $headCrumb([$parent->slug])
            ),
            'inventory.warehouses.show.warehouse-areas.show.locations.index' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs('inventory.warehouses.show.warehouse-areas.show', $parent),
                $headCrumb([$parent->warehouse->slug, $parent->slug])
            ),

            default => []
        };
    }
}
