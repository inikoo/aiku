<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Warehouse $warehouse
 */
class ShowWarehouse extends InertiaAction
{
    use HasUIWarehouse;
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouses.edit');

        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function asController(Warehouse $warehouse, ActionRequest $request): void
    {
        $this->initialisation($request);
        $this->warehouse    = $warehouse;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Inventory/Warehouse',
            [
                'title'       => __('warehouse'),
                'breadcrumbs' => $this->getBreadcrumbs($this->warehouse),
                'pageHead'    => [
                    'icon'  => 'fal fa-warehouse',
                    'title' => $this->warehouse->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'meta'  => [
                        [
                            'name'     => trans_choice('warehouse area|warehouse areas', $this->warehouse->stats->number_warehouse_areas),
                            'number'   => $this->warehouse->stats->number_warehouse_areas,
                            'href'     => [
                                'inventory.warehouses.show.warehouse_areas.index',
                                $this->warehouse->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('warehouse areas')
                            ]
                        ],
                        [
                            'name'     => trans_choice('location|locations', $this->warehouse->stats->number_locations),
                            'number'   => $this->warehouse->stats->number_locations,
                            'href'     => [
                                'inventory.warehouses.show.locations.index',
                                $this->warehouse->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-inventory',
                                'tooltip' => __('locations')
                            ]
                        ]
                    ]

                ],
                'tabs' => [

                    'current' => 'dashboard',
                    'items'   => [
                        'dashboard' => [
                            'name'    => __('dashboard'),
                            'icon'    => 'fal fa-tachometer-alt',
                            'content' => 'content dashboard',
                        ],
                        'details'   => [
                            'name'    => __('details'),
                            'icon'    => 'fal fa-clock',
                            'content' => 'content details',
                        ],
                        'history'   => [
                            'name'    => __('changelog'),
                            'icon'    => 'fal fa-clock',
                            'content' => 'content changelog',
                        ]
                    ]


                ],
                'warehouse'   => $this->warehouse,
            ]
        );
    }


    public function jsonResponse(): WarehouseResource
    {
        return new WarehouseResource($this->warehouse);
    }

}
