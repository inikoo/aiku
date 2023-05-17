<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 11 Apr 2023 08:24:46 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\InertiaAction;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateLocation extends InertiaAction
{
    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('new location'),
                'pageHead'    => [
                    'title'        => __('new location'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'inventory.locations.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('create location'),
                            'fields' => [

                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => ''
                                ],
                            ]
                        ]
                    ],
                    'route'      => match ($request->route()->getName()) {
                'inventory.warehouses.show.locations.create' => [
                    'name' => 'models.warehouse.location.store',
                    'arguments' => [
                        $request->route()->parameter('warehouse')->slug
                    ]
                ],
                        'inventory.warehouses.show.warehouse-areas.show.locations.create' => [
                            'name' => 'models.warehouse-area.location.store',
                            'arguments' => [
                                $request->route()->parameter('warehouseArea')->slug
                            ]
                        ]
                    }
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('inventory');
    }


    public function inWarehouse(Warehouse $warehouse, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }


    public function inWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexLocations::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('creating location'),
                    ]
                ]
            ]
        );
    }
}
