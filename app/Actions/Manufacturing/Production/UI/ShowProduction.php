<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Manufacturing\Production\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Warehouse\UI\GetWarehouseShowcase;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Enums\UI\Inventory\WarehouseTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\Tag\TagResource;
use App\Models\Helpers\Tag;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProduction extends OrgAction
{
    use WithActionButtons;

    public function handle(Warehouse $warehouse): Warehouse
    {
        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("supervisor-warehouses.{$this->warehouse->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("locations.{$this->warehouse->id}.edit");

        return $request->user()->hasPermissionTo("locations.{$this->warehouse->id}.view");
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): Warehouse
    {
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(WarehouseTabsEnum::values());

        return $this->handle($warehouse);
    }


    public function htmlResponse(Warehouse $warehouse, ActionRequest $request): Response
    {
        $routeParameters = $request->route()->originalParameters();

        return Inertia::render(
            'Org/Warehouse/Warehouse',
            [
                'title'                            => __('warehouse'),
                'breadcrumbs'                      => $this->getBreadcrumbs($request->route()->originalParameters()),
                'navigation'                       => [
                    'previous' => $this->getPrevious($warehouse, $request),
                    'next'     => $this->getNext($warehouse, $request),
                ],
                'pageHead'                         => [
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'warehouse'],
                            'title' => __('warehouse')
                        ],
                    'title'   => $warehouse->name,
                    'actions' => [
                        $this->canEdit ?
                            [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new location'),
                                'label'   => __('new location'),
                                'route'   => [
                                    'name'       => 'grp.org.warehouses.show.infrastructure.locations.create',
                                    'parameters' => $request->route()->originalParameters()
                                ]
                            ]
                            : null,
                        $this->canDelete ? $this->getDeleteActionIcon($request) : null,
                        $this->canEdit ? $this->getEditActionIcon($request) : null,

                    ],
                    'meta'    => [
                        [
                            'name'     => trans_choice('warehouse area|warehouse areas', $warehouse->stats->number_warehouse_areas),
                            'number'   => $warehouse->stats->number_warehouse_areas,
                            'href'     => [
                                'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.index',
                                'parameters' => array_merge($routeParameters, [$warehouse->slug])
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('warehouse areas')
                            ]
                        ],
                        [
                            'name'     => trans_choice('location|locations', $warehouse->stats->number_locations),
                            'number'   => $warehouse->stats->number_locations,
                            'href'     => [
                                'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                                'parameters' => array_merge($routeParameters, [$warehouse->slug])
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-inventory',
                                'tooltip' => __('locations')
                            ]
                        ]
                    ]

                ],
                'tabs'                             => [

                    'current'    => $this->tab,
                    'navigation' => WarehouseTabsEnum::navigation(),
                ],
                'tagsList'      => TagResource::collection(Tag::all()),

                WarehouseTabsEnum::SHOWCASE->value => $this->tab == WarehouseTabsEnum::SHOWCASE->value ?
                    fn () => GetWarehouseShowcase::run($warehouse)
                    : Inertia::lazy(fn () => GetWarehouseShowcase::run($warehouse)),

                WarehouseTabsEnum::WAREHOUSE_AREAS->value => $this->tab == WarehouseTabsEnum::WAREHOUSE_AREAS->value
                    ?
                    fn () => WarehouseAreaResource::collection(
                        IndexWarehouseAreas::run(
                            parent: $warehouse,
                            prefix: WarehouseTabsEnum::WAREHOUSE_AREAS->value
                        )
                    )
                    : Inertia::lazy(fn () => WarehouseAreaResource::collection(
                        IndexWarehouseAreas::run(
                            parent: $warehouse,
                            prefix: WarehouseTabsEnum::WAREHOUSE_AREAS->value
                        )
                    )),

                WarehouseTabsEnum::LOCATIONS->value => $this->tab == WarehouseTabsEnum::LOCATIONS->value
                    ?
                    fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $warehouse,
                            prefix:  WarehouseTabsEnum::LOCATIONS->value
                        )
                    )
                    : Inertia::lazy(fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $warehouse,
                            prefix:  WarehouseTabsEnum::LOCATIONS->value
                        )
                    )),

                WarehouseTabsEnum::HISTORY->value => $this->tab == WarehouseTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($warehouse))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($warehouse)))

            ]
        )->table(
            IndexWarehouseAreas::make()->tableStructure(
                parent: $warehouse,
                prefix: WarehouseTabsEnum::WAREHOUSE_AREAS->value
                /* modelOperations: [
                      'createLink' => $this->canEdit ? [
                          'route' => [
                              'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.create',
                              'parameters' => array_values([$warehouse->slug])
                          ],
                          'label' => __('area'),
                          'style' => 'create'
                      ] : false,
                  ],
                  prefix: 'warehouse_areas' */
            )
        )->table(
            IndexLocations::make()->tableStructure(
                parent: $warehouse,

                /* modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.infrastructure.locations.create',
                            'parameters' => array_values([$warehouse->slug])
                        ],
                        'label' => __('location'),
                        'style' => 'create'
                    ] : false
                ], */
                prefix: WarehouseTabsEnum::LOCATIONS->value
            )
        )->table(IndexHistory::make()->tableStructure());
    }


    public function jsonResponse(Warehouse $warehouse): WarehouseResource
    {
        return new WarehouseResource($warehouse);
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $warehouse = Warehouse::where('slug', $routeParameters['warehouse'])->first();

        return array_merge(
            (new ShowOrganisationDashboard())->getBreadcrumbs(Arr::only($routeParameters, 'organisation')),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.warehouses.index',
                                'parameters' => $routeParameters['organisation']
                            ],
                            'label' => __('warehouse'),
                            'icon'  => 'fal fa-bars'
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.infrastructure.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => $warehouse?->code,
                            'icon'  => 'fal fa-bars'
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(Warehouse $warehouse, ActionRequest $request): ?array
    {
        $previous = Warehouse::where('code', '<', $warehouse->code)->where('organisation_id', $warehouse->organisation_id)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Warehouse $warehouse, ActionRequest $request): ?array
    {
        $next = Warehouse::where('code', '>', $warehouse->code)->where('organisation_id', $warehouse->organisation_id)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Warehouse $warehouse, string $routeName): ?array
    {
        if (!$warehouse) {
            return null;
        }

        return match ($routeName) {
            'grp.org.warehouses.show.infrastructure.dashboard' => [
                'label' => $warehouse->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'warehouse'    => $warehouse->slug
                    ]

                ]
            ]
        };
    }
}
