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
    public function handle(Warehouse $warehouse, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('new warehouse area'),
                'pageHead'    => [
                    'title'        => __('new warehouse area'),
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/create$/', 'index', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('ID/Name'),
                            'fields' => [

                                'code' => [
                                    'type'        => 'input',
                                    'label'       => __('code'),
                                    'placeholder' => __('maximum 4 character long'),
                                    'value'       => '',
                                    'required'    => true,
                                ],
                                'name' => [
                                    'type'    => 'input',
                                    'label'   => __('name'),
                                    'value'   => '',
                                    'required'=> true
                                ],

                            ]
                        ],

                    ],
                    'route'     => [
                        'name'      => 'grp.models.warehouse.warehouse-area.store',
                        'arguments' => $warehouse->id
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('inventory.warehouse-areas.edit');
    }


    public function asController(Warehouse $warehouse, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($warehouse, $request);
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
                        'label' => __('Creating warehouse areas'),
                    ]
                ]
            ]
        );
    }
}
