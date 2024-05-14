<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:50 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Manufacturing\Artifact\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Inventory\Artifact\UI\GetArtifactShowcase;
use App\Actions\OrgAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Enums\UI\Inventory\ArtifactTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\ArtifactResource;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\Artifact;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowArtifact extends OrgAction
{
    use WithActionButtons;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("inventory.{$this->warehouse->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("inventory.{$this->warehouse->id}.edit");

        return $request->user()->hasPermissionTo("inventory.{$this->warehouse->id}.view");
    }


    public function asController(Organisation $organisation, Warehouse $warehouse, Artifact $artifact, ActionRequest $request): Artifact
    {
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(ArtifactTabsEnum::values());

        return $artifact;
    }

    public function htmlResponse(Artifact $artifact, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Warehouse/Artifact',
            [
                'title'                                => __('warehouse area'),
                'breadcrumbs'                          => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'                           => [
                    'previous' => $this->getPrevious($artifact, $request),
                    'next'     => $this->getNext($artifact, $request),
                ],
                'pageHead'                             => [
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'fa-map-signs'],
                            'title' => __('warehouse area')
                        ],
                    'title'   => $artifact->name,
                    'actions' => [
                        $this->canEdit ?
                            [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new location'),
                                'label'   => __('new location'),
                                'route'   => [
                                    'name'       => $request->route()->getName().'.locations.create',
                                    'parameters' => $request->route()->originalParameters()
                                ]
                            ]
                            : null,
                        $this->canDelete ? $this->getDeleteActionIcon($request) : null,
                        $this->canEdit ? $this->getEditActionIcon($request) : null,
                    ],
                    'meta'    => [
                        [
                            'name'     => trans_choice('location|locations', $artifact->stats->number_locations),
                            'number'   => $artifact->stats->number_locations,
                            'href'     => [
                                'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-inventory',
                                'tooltip' => __('locations')
                            ]
                        ]
                    ]
                ],
                'tabs'                                 => [
                    'current'    => $this->tab,
                    'navigation' => ArtifactTabsEnum::navigation()
                ],
                ArtifactTabsEnum::SHOWCASE->value => $this->tab == ArtifactTabsEnum::SHOWCASE->value ?
                    fn () => GetArtifactShowcase::run($artifact)
                    : Inertia::lazy(fn () => GetArtifactShowcase::run($artifact)),

                ArtifactTabsEnum::LOCATIONS->value => $this->tab == ArtifactTabsEnum::LOCATIONS->value
                    ?
                    fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $artifact,
                            prefix: ArtifactTabsEnum::LOCATIONS->value
                        )
                    )
                    : Inertia::lazy(fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $artifact,
                            prefix: ArtifactTabsEnum::LOCATIONS->value
                        )
                    )),

                ArtifactTabsEnum::HISTORY->value => $this->tab == ArtifactTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($artifact))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($artifact)))

            ]
        )->table(
            IndexLocations::make()->tableStructure(
                parent: $artifact,
                prefix: ArtifactTabsEnum::LOCATIONS->value
            )
        )->table(IndexHistory::make()->tableStructure());
    }


    public function jsonResponse(Artifact $artifact): ArtifactResource
    {
        return new ArtifactResource($artifact);
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (Artifact $artifact, array $routeParameters, $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('warehouse areas')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $artifact->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ];
        };

        $artifact = Artifact::where('slug', $routeParameters['artifact'])->first();

        return array_merge(
            (new ShowWarehouse())->getBreadcrumbs($routeParameters),
            $headCrumb(
                $artifact,
                [
                    'index' => [
                        'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.index',
                        'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse'])
                    ],
                    'model' => [
                        'name'       => 'grp.org.warehouses.show.infrastructure.warehouse-areas.show',
                        'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse', 'artifact'])
                    ]
                ],
                $suffix
            )
        );
    }

    public function getPrevious(Artifact $artifact, ActionRequest $request): ?array
    {
        $previous = Artifact::where('code', '<', $artifact->code)->when(true, function ($query) use ($artifact, $request) {
            $query->where('warehouse_id', $artifact->warehouse_id);
        })->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Artifact $artifact, ActionRequest $request): ?array
    {
        $next = Artifact::where('code', '>', $artifact->code)->when(true, function ($query) use ($artifact, $request) {
            $query->where('warehouse_id', $artifact->warehouse->id);
        })->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Artifact $artifact, string $routeName): ?array
    {
        if (!$artifact) {
            return null;
        }

        return [
            'label' => $artifact->name,
            'route' => [
                'name'       => $routeName,
                'parameters' => [
                    'organisation'  => $artifact->organisation->slug,
                    'warehouse'     => $artifact->warehouse->slug,
                    'artifact'      => $artifact->slug
                ]

            ]
        ];
    }

}
