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

    public function htmlResponse(Warehouse $warehouse, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'                            => __('edit warehouse'),
                'breadcrumbs'                      => $this->getBreadcrumbs($warehouse),
                'navigation'                       => [
                    'previous' => $this->getPrevious($warehouse, $request),
                    'next'     => $this->getNext($warehouse, $request),
                ],
                'pageHead'    => [
                    'title'     => $warehouse->code,
                    'actions'   => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
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
                                    'value' => $warehouse->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                    'value' => $warehouse->name
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'grp.models.warehouse.update',
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

    public function getPrevious(Warehouse $warehouse, ActionRequest $request): ?array
    {
        $previous = Warehouse::where('code', '<', $warehouse->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Warehouse $warehouse, ActionRequest $request): ?array
    {
        $next = Warehouse::where('code', '>', $warehouse->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Warehouse $warehouse, string $routeName): ?array
    {
        if (!$warehouse) {
            return null;
        }

        return match ($routeName) {
            'grp.inventory.warehouses.edit' => [
                'label' => $warehouse->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'warehouse' => $warehouse->slug
                    ]

                ]
            ]
        };
    }
}
