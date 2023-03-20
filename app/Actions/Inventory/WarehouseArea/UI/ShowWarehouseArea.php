<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:50 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property WarehouseArea $warehouseArea
 */
class ShowWarehouseArea extends InertiaAction
{
    use HasUIWarehouseArea;

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->routeName    = $request->route()->getName();
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouse-areas.edit');
        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function inTenant(WarehouseArea $warehouseArea, ActionRequest $request): void
    {
        $this->warehouseArea = $warehouseArea;
        $this->initialisation($request);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): void
    {
        $this->warehouseArea = $warehouseArea;
        $this->initialisation($request);
    }

    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Inventory/WarehouseArea',
            [
                'title'         => __('warehouse area'),
                'breadcrumbs'   => $this->getBreadcrumbs($this->routeName, $this->warehouseArea),
                'pageHead'      => [
                    'icon'  => 'fal fa-map-signs',
                    'title' => $this->warehouseArea->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'meta'  => [
                        [
                            'name'     => trans_choice('location|locations', $this->warehouseArea->stats->number_locations),
                            'number'   => $this->warehouseArea->stats->number_locations,
                            'href'     =>
                                match ($this->routeName) {
                                    'inventory.warehouses.show.warehouse-areas.show' => [
                                        'inventory.warehouses.show.warehouse-areas.show.locations.index',
                                        [$this->warehouseArea->warehouse->slug, $this->warehouseArea->slug]
                                    ],
                                    default => [
                                        'inventory.warehouse-areas.show.locations.index',
                                        $this->warehouseArea->slug
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


    public function jsonResponse(): LocationResource
    {
        return new LocationResource($this->warehouseArea);
    }
}
