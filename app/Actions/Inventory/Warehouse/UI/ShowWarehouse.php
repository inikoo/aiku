<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;
use App\Enums\UI\WarehouseTabsEnum;
use App\Http\Resources\Inventory\WarehouseResource;
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
        $this->warehouse = $warehouse;
        $this->tab       = $request->input('tab', WarehouseTabsEnum::STATS->value);
    }


    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Inventory/Warehouse/Warehouse',
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
                                'inventory.warehouses.show.warehouse-areas.index',
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
                'tabs'        => [

                    'current'    => $this->tab,
                    'navigation' => [
                        WarehouseTabsEnum::STATS->value           => [
                            'title' => __('stats'),
                            'icon'  => 'fal fa-chart-line',
                        ],
                        WarehouseTabsEnum::WAREHOUSE_AREAS->value => [
                            'title' => __('warehouse areas'),
                            'icon'  => 'fal fa-map-signs',
                        ],
                        WarehouseTabsEnum::LOCATIONS->value       => [
                            'title' => __('locations'),
                            'icon'  => 'fal fa-inventory',
                        ],
                        WarehouseTabsEnum::DATA->value            => [
                            'title' => __('data'),
                            'icon'  => 'fal fa-database',
                        ],
                        WarehouseTabsEnum::CHANGELOG->value       => [

                            'title' => __('changelog'),
                            'icon'  => 'fal fa-clock',


                        ]

                    ],


                ],


                WarehouseTabsEnum::LOCATIONS->value => $this->tab == WarehouseTabsEnum::LOCATIONS->value ?
                    fn () => IndexLocations::run($this->warehouse)
                    : Inertia::lazy(fn () => IndexLocations::run($this->warehouse)),

                WarehouseTabsEnum::WAREHOUSE_AREAS->value => $this->tab == WarehouseTabsEnum::WAREHOUSE_AREAS->value ?
                    fn () => IndexWarehouseAreas::run($this->warehouse)
                    : Inertia::lazy(fn () => IndexWarehouseAreas::run($this->warehouse)),

            ]
        )->table(IndexLocations::make()->locationsTableStructure())
            ->table(IndexWarehouseAreas::make()->warehouseAreasTableStructure());
    }


    public function jsonResponse(): WarehouseResource
    {
        return new WarehouseResource($this->warehouse);
    }
}
