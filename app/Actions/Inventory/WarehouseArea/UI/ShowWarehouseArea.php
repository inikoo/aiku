<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:50 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Enums\UI\WarehouseAreaTabsEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\WarehouseAreaResource;
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

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouse-areas.edit');
        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function inTenant(WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request)->withTab(WarehouseAreaTabsEnum::values());
        return $warehouseArea;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request)->withTab(WarehouseAreaTabsEnum::values());
        return $warehouseArea;
    }

    public function htmlResponse(WarehouseArea $warehouseArea, ActionRequest $request): Response
    {
        return Inertia::render(
            'Inventory/WarehouseArea',
            [
                'title'         => __('warehouse area'),
                'breadcrumbs'   => $this->getBreadcrumbs($this->routeName, $warehouseArea),
                'pageHead'      => [
                    'icon'  => 'fal fa-map-signs',
                    'title' => $warehouseArea->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'meta'  => [
                        [
                            'name'     => trans_choice('location|locations', $warehouseArea->stats->number_locations),
                            'number'   => $warehouseArea->stats->number_locations,
                            'href'     =>
                                match ($this->routeName) {
                                    'inventory.warehouses.show.warehouse-areas.show' => [
                                        'inventory.warehouses.show.warehouse-areas.show.locations.index',
                                        [$warehouseArea->warehouse->slug, $warehouseArea->slug]
                                    ],
                                    default => [
                                        'inventory.warehouse-areas.show.locations.index',
                                        $warehouseArea->slug
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
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => WarehouseAreaTabsEnum::navigation()
                ],
                WarehouseAreaTabsEnum::LOCATIONS->value => $this->tab == WarehouseAreaTabsEnum::LOCATIONS->value ?
                    fn () => LocationResource::collection(IndexLocations::run($warehouseArea))
                    : Inertia::lazy(fn () => LocationResource::collection(IndexLocations::run($warehouseArea))),



            ]
        )->table(IndexLocations::make()->tableStructure());
    }


    public function jsonResponse(WarehouseArea $warehouseArea): WarehouseAreaResource
    {
        return new WarehouseAreaResource($warehouseArea);
    }
}
