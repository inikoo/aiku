<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\InertiaAction;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditWarehouse extends InertiaAction
{
    public function handle(Warehouse $warehouse): Warehouse
    {
        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }

    public function asController(Warehouse $warehouse, ActionRequest $request): Warehouse
    {
        $this->initialisation($request);
        return $this->handle($warehouse);
    }

    public function htmlResponse(Warehouse $warehouse): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('edit warehouse'),
                'breadcrumbs' => $this->getBreadcrumbs($warehouse),
                'pageHead'    => [
                    'title'     => $warehouse->code,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $warehouse->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $warehouse->name
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.warehouse.update',
                            'parameters'=> $warehouse->slug

                        ],
                    ]
                ]
            ]
        );
    }



    public function getBreadcrumbs(Warehouse $warehouse): array
    {
        return ShowWarehouse::make()->getBreadcrumbs(warehouse:$warehouse, suffix: '('.__('editing').')');
    }
}
