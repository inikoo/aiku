<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\UI\ShowWarehouseArea;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Models\Inventory\Location;

trait HasUILocation
{
    public function getBreadcrumbs(string $routeName, Location $location): array
    {
        $headCrumb = function (array $routeParameters = []) use ($location, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $location->code,
                    'index'           => [
                        'route'           => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('locations list')
                    ],
                    'modelLabel'      => [
                        'label' => __('location')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'inventory.locations.show' => array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb([$location->slug])
            ),
            'inventory.warehouses.show.locations.show' => array_merge(
                (new ShowWarehouse())->getBreadcrumbs($location->warehouse),
                $headCrumb([$location->warehouse->slug, $location->slug])
            ),
            'inventory.warehouse-areas.show.locations.show' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs('inventory.warehouse-areas.show', $location->warehouseArea),
                $headCrumb([$location->warehouseArea->slug, $location->slug])
            ),
            'inventory.warehouses.show.warehouse-areas.show.locations.show' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs('inventory.warehouses.show.warehouse-areas.show', $location->warehouseArea),
                $headCrumb([$location->warehouse->slug, $location->warehouseArea->slug, $location->slug])
            ),

            default => []
        };
    }
}
