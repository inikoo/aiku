<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 15 Sept 2022 14:41:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Actions\InertiaAction;
use App\Actions\Inventory\ShowInventoryDashboard;
use App\Actions\Inventory\Warehouse\ShowWarehouse;
use App\Actions\Inventory\WarehouseArea\ShowWarehouseArea;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;


/**
 * @property Location $location
 */
class ShowLocation extends InertiaAction
{


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.view");
    }


    public function inOrganisation(Location $location): void
    {
        $this->location = $location;
        $this->validateAttributes();
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseArea(WarehouseArea $warehouseArea,Location $location): void
    {
        $this->location = $location;
        $this->validateAttributes();
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function InWarehouseInWarehouseArea(Warehouse $warehouse,WarehouseArea $warehouseArea,Location $location): void
    {
        $this->location = $location;
        $this->validateAttributes();
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, Location $location): void
    {
        $this->location = $location;
        $this->validateAttributes();
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Inventory/ShowLocation',
            [
                'title'       => __('location'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->location),
                'pageHead'    => [
                    'icon'  => 'fal fa-inventory',
                    'title' => $this->location->code,


                ],
                'location'    => $this->location
            ]
        );
    }


    public function jsonResponse(): JsonResource
    {
        return new JsonResource($this->location);
    }


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
                (new ShowInventoryDashboard())->getBreadcrumbs(),
                $headCrumb([$location->id])
            ),
            'inventory.warehouses.show.locations.show' => array_merge(
                (new ShowWarehouse())->getBreadcrumbs($location->warehouse),
                $headCrumb([$location->warehouse_id,$location->id])
            ),
            'inventory.warehouse_areas.show.locations.show' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs('inventory.warehouse_areas.show', $location->warehouseArea),
                $headCrumb([$location->warehouse_area_id,$location->id])
            ),
            'inventory.warehouses.show.warehouse_areas.show.locations.show' => array_merge(
                (new ShowWarehouseArea())->getBreadcrumbs('inventory.warehouses.show.warehouse_areas.show', $location->warehouseArea),
                $headCrumb([$location->warehouse_id, $location->warehouse_area_id,$location->id])
            ),

            default => []
        };
    }

}
