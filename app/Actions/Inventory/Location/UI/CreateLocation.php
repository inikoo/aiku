<?php

/** @noinspection PhpUnusedParameterInspection */

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 11 Apr 2023 08:24:46 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\InertiaAction;
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
                'title'    => __('new location'),
                'pageHead' => [
                    'title'        => __('new location'),
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.locations.index',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [

                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => '',
                                    'required' => true
                                ],
                            ]
                        ],
                        [
                            'title'  => __('capacity'),
                            'icon'   => 'fa-light fa-phone',
                            'fields' => [
                                'max_weight' => [
                                    'type'  => 'input',
                                    'label' => __('max weight (kg)'),
                                    'value' => '',
                                ],
                                'max_volume' => [
                                    'type'  => 'input',
                                    'label' => __('max volume (m³)'),
                                    'value' => '',
                                ],
                            ]
                        ],

                       /* [
                            'title'  => __('picking pipelines'),
                            'fields' => [
                                'drop_shipping_area' => [
                                    'type'  => 'input',
                                    'label' => __('DS'),
                                    'value' => '',
                                ],
                            ]
                        ],
                        [
                            'title'  => __('operations'),
                            'fields' => [
                                'delete_at' => [
                                    'type'  => 'input',
                                    'label' => __('delete location'),
                                    'value' => '',
                                ],
                            ]
                        ], */


                    ],
                    'route' => match ($request->route()->getName()) {
                        'grp.org.warehouses.show.locations.create' => [
                            'name'      => 'grp.models.warehouse.location.store',
                            'arguments' => [$request->route()->parameters['warehouse']->slug]
                        ],
                        default => [
                            'name'      => 'grp.models.warehouse-area.location.store',
                            'arguments' => [
                                $request->route()->parameters['warehouseArea']->slug
                            ]
                        ]
                    }
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('inventory');
    }


    public function inWarehouse(Warehouse $warehouse, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }

    public function inWarehouseArea(WarehouseArea $warehouseArea, ActionRequest $request): Response
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
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating location'),
                    ]
                ]
            ]
        );
    }
}
