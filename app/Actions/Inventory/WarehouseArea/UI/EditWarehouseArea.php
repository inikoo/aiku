<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:09:31 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\OrgAction;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditWarehouseArea extends OrgAction
{
    public function handle(WarehouseArea $warehouseArea): WarehouseArea
    {
        return $warehouseArea;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("inventory.{$this->organisation->id}.edit");
        return $request->user()->hasPermissionTo("inventory.{$this->organisation->id}.edit");
    }

    public function asController(Organisation $organisation, $shop, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($warehouseArea->organisation, $request);

        return $this->handle($warehouseArea);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisationFromWarehouse($warehouse, $request);
        return $this->handle($warehouseArea);
    }

    public function inOrganisation(Organisation $organisation, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($organisation, $request);

        return $this->handle($warehouseArea);
    }

    public function htmlResponse(WarehouseArea $warehouseArea, ActionRequest $request): Response
    {
        // dd($warehouseArea);
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('warehouse areas'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
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
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        "properties" => [
                            'label' => __('properties'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $warehouseArea->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                    'value' => $warehouseArea->name
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'grp.models.warehouse-area.update',
                            'parameters' => $warehouseArea->id

                        ],
                    ]
                ]

            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return ShowWarehouseArea::make()->getBreadcrumbs(
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
    }

    public function getPrevious(WarehouseArea $warehouseArea, ActionRequest $request): ?array
    {
        $previous = WarehouseArea::where('code', '<', $warehouseArea->code)->when(true, function ($query) use ($warehouseArea, $request) {
            if ($request->route()->getName() == 'grp.oms.warehouses.show.warehouse-areas.edit') {
                $query->where('warehouse_id', $warehouseArea->warehouse_id);
            }
        })->orderBy('code', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(WarehouseArea $warehouseArea, ActionRequest $request): ?array
    {
        $next = WarehouseArea::where('code', '>', $warehouseArea->code)->when(true, function ($query) use ($warehouseArea, $request) {
            if ($request->route()->getName() == 'grp.oms.warehouses.show.warehouse-areas.edit') {
                $query->where('warehouse_id', $warehouseArea->warehouse->id);
            }
        })->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?WarehouseArea $warehouseArea, string $routeName): ?array
    {
        // dd($routeName);
        if (!$warehouseArea) {
            return null;
        }

        return match ($routeName) {
            'grp.oms.warehouse-areas.edit' => [
                'label' => $warehouseArea->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'warehouseArea' => $warehouseArea->slug
                    ]
                ]
            ],
            'grp.org.warehouses.show.infrastructure.warehouse-areas.edit' => [
                'label' => $warehouseArea->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        $this->organisation,
                        $warehouseArea->warehouse,
                        $warehouseArea->slug
                    ]

                ]
            ],
            'grp.oms.warehouses.show.warehouse-areas.edit' => [
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
