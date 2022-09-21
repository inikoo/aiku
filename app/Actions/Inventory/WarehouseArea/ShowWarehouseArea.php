<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 16 Sept 2022 12:42:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\Inventory\ShowInventoryDashboard;
use App\Actions\Inventory\Warehouse\ShowWarehouse;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property WarehouseArea $warehouseArea
 */
class ShowWarehouseArea
{
    use AsAction;
    use WithInertia;


    private ?string $routeName = null;

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->routeName    = $request->route()->getName();
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function inOrganisation(WarehouseArea $warehouseArea): void
    {
        $this->warehouseArea = $warehouseArea;
        $this->validateAttributes();
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse,WarehouseArea $warehouseArea): void
    {
        $this->warehouseArea = $warehouseArea;
        $this->validateAttributes();
    }

    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Inventory/ShowWarehouseArea',
            [
                'title'         => __('warehouse area'),
                'breadcrumbs'   => $this->getBreadcrumbs($this->routeName, $this->warehouseArea),
                'pageHead'      => [
                    'icon'  => 'fal fa-map-signs',
                    'title' => $this->warehouseArea->name,
                    'meta'  => [
                        [
                            'name'     => trans_choice('location|locations', $this->warehouseArea->stats->number_locations),
                            'number'   => $this->warehouseArea->stats->number_locations,
                            'href'     =>
                                match ($this->routeName) {
                                    'inventory.warehouses.show.warehouse_areas.show' => [
                                        'inventory.warehouses.show.warehouse_areas.show.locations.index',
                                        [$this->warehouseArea->warehouse_id, $this->warehouseArea->id]
                                    ],
                                    default => [
                                        'inventory.warehouse_areas.show.locations.index',
                                        $this->warehouseArea->id
                                    ]
                                }


                            ,
                            'leftIcon' => [
                                'icon'    => 'fal fa-inventory',
                                'tooltip' => __('locations')
                            ]
                        ]
                    ]

                ],
                'warehouseArea' => $this->warehouseArea
            ]
        );
    }


    #[Pure] public function jsonResponse(): LocationResource
    {
        return new LocationResource($this->warehouseArea);
    }


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
                (new ShowInventoryDashboard())->getBreadcrumbs(),
                $headCrumb([$warehouseArea->id])

            ),
            'inventory.warehouses.show.warehouse_areas.show' => array_merge(
                (new ShowWarehouse())->getBreadcrumbs($warehouseArea->warehouse),
                $headCrumb([$warehouseArea->warehouse_id, $warehouseArea->id])
            ),
            default => []
        };
    }

}
