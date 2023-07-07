<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:09:31 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditWarehouseArea extends InertiaAction
{
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

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request);
        return $this->handle($warehouseArea);
    }

    public function inTenant(WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request);

        return $this->handle($warehouseArea);
    }

    public function htmlResponse(WarehouseArea $warehouseArea, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('warehouse areas'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($warehouseArea, $request),
                    'next'     => $this->getNext($warehouseArea, $request),
                ],
                'pageHead'    => [
                    'title'     => $warehouseArea->name,
                    'icon'      => [
                        'title' => __('warehouses areas'),
                        'icon'  => 'fal fa-map-signs'
                    ],
                    'actions'   => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
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
                            'name'      => 'models.warehouse-area.update',
                            'parameters'=> $warehouseArea->slug

                        ],
                    ]
                ]

            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowWarehouseArea::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('editing').')'
        );
    }

    public function getPrevious(WarehouseArea $warehouseArea, ActionRequest $request): ?array
    {
        $previous = WarehouseArea::where('code', '<', $warehouseArea->code)->when(true, function ($query) use ($warehouseArea, $request) {
            if ($request->route()->getName() == 'inventory.warehouses.show.warehouse-areas.edit') {
                $query->where('warehouse_id', $warehouseArea->warehouse_id);
            }
        })->orderBy('code', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(WarehouseArea $warehouseArea, ActionRequest $request): ?array
    {
        $next = WarehouseArea::where('code', '>', $warehouseArea->code)->when(true, function ($query) use ($warehouseArea, $request) {
            if ($request->route()->getName() == 'inventory.warehouses.show.warehouse-areas.edit') {
                $query->where('warehouse_id', $warehouseArea->warehouse->id);
            }
        })->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?WarehouseArea $warehouseArea, string $routeName): ?array
    {
        if (!$warehouseArea) {
            return null;
        }

        return match ($routeName) {
            'inventory.warehouse-areas.edit' => [
                'label' => $warehouseArea->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'warehouseArea' => $warehouseArea->slug
                    ]

                ]
            ],
            'inventory.warehouses.show.warehouse-areas.edit' => [
                'label' => $warehouseArea->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'warehouse'     => $warehouseArea->warehouse->slug,
                        'warehouseArea' => $warehouseArea->slug
                    ]

                ]
            ]
        };
    }
}
