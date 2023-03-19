<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:09:31 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Models\Inventory\WarehouseArea;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditWarehouseArea extends InertiaAction
{
    use HasUIWarehouseArea;
    public function handle(WarehouseArea $warehouseArea): WarehouseArea
    {
        return $warehouseArea;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouse-areas.edit');
        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }

    public function asController(WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request);

        return $this->handle($warehouseArea);
    }

    public function inTenant(WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request);

        return $this->handle($warehouseArea);
    }

    public function htmlResponse(WarehouseArea $warehouseArea): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('warehouse areas'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $warehouseArea),
                'pageHead'    => [
                    'title'     => $warehouseArea->code,
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
                                    'value' => $warehouseArea->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $warehouseArea->name
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.warehouse_area.update',
                            'parameters'=> $warehouseArea->slug

                        ],
                    ]
                ]

            ]
        );
    }

    public function jsonResponse(WarehouseArea $warehouseArea): WarehouseAreaResource
    {
        return new WarehouseAreaResource($warehouseArea);
    }
}
