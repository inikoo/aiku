<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Models\Inventory\WarehouseArea;

trait HasUIWarehouseArea
{
    public function getBreadcrumbs(string $routeName, WarehouseArea $warehouseArea): array
    {
        $headCrumb = function (array $routeParameters = []) use ($warehouseArea, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $warehouseArea->code,
                    'index'           => [
                        'route'           => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('warehouse areas list')
                    ],
                    'modelLabel'      => [
                        'label' => __('area')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'inventory.warehouse_areas.show' => array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb([$warehouseArea->slug])
            ),
            'inventory.warehouses.show.warehouse_areas.show' => array_merge(
                (new ShowWarehouse())->getBreadcrumbs($warehouseArea->warehouse),
                $headCrumb([$warehouseArea->warehouse->slug, $warehouseArea->slug])
            ),
            default => []
        };
    }
}
