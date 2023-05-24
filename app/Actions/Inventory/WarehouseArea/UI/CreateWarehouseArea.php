<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:34:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateWarehouseArea extends InertiaAction
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
                'title'       => __('new warehouse area'),
                'pageHead'    => [
                    'title'        => __('new warehouse area'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'inventory.warehouse-areas.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('create warehouse'),
                            'fields' => [

                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => ''
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                    'value' => ''
                                ],

                            ]
                        ],
                        [
                            'title'  => __('locations'),
                            'fields' => [

                                'area_type' => [
                                    'type'  => 'input',
                                    'label' => __('area type'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('operations'),
                            'fields' => [

                                'delete_at' => [
                                    'type'  => 'input',
                                    'label' => __('delete'),
                                    'value' => ''
                                ],
                            ]
                        ],
                    ],
                    'route'     => [
                        'name'      => 'models.warehouse.warehouse-area.store',
                        'arguments' => [$request->route()->parameters['warehouse']->slug]
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('inventory.warehouse-areas.edit');
    }


    public function asController(Warehouse $warehouse, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }



    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexWarehouseAreas::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating warehouse areas'),
                    ]
                ]
            ]
        );
    }
}
