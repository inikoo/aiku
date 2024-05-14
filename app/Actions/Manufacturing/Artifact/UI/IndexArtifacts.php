<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:46 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Manufacturing\Artifact\UI;

use App\Actions\Inventory\Production\UI\ShowProduction;
use App\Actions\OrgAction;
use App\Actions\UI\Inventory\ShowInventoryDashboard;
use App\Enums\UI\Inventory\ProductionTabsEnum;
use App\Http\Resources\Inventory\ProductionAreaResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\Production;
use App\Models\Inventory\ProductionArea;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexArtifacts extends OrgAction
{
    protected Production|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            $this->canEdit = $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);

            return $request->user()->hasAnyPermission(
                [
                    'productions-view.'.$this->organisation->id,
                    'org-supervisor.'.$this->organisation->id
                ]
            );
        }

        $this->canEdit = $request->user()->hasPermissionTo("locations.{$this->production->id}.edit");

        return $request->user()->hasPermissionTo("locations.{$this->production->id}.edit");
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(ProductionTabsEnum::values());

        return $this->handle($organisation);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $production;
        $this->initialisationFromProduction($production, $request)->withTab(ProductionTabsEnum::values());

        return $this->handle($production);
    }

    public function handle(Production|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('production_areas.code', $value)
                    ->whereWith('production_areas.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(ProductionArea::class);


        return $queryBuilder
            ->defaultSort('production_areas.code')
            ->select(
                [
                    'production_areas.code',
                    'production_areas.id',
                    'production_areas.name',
                    'number_locations',
                    'productions.slug as production_slug',
                    'production_areas.slug'
                ]
            )
            ->leftJoin('production_area_stats', 'production_area_stats.production_area_id', 'production_areas.id')
            ->leftJoin('productions', 'production_areas.production_id', 'productions.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Production') {
                    $query->where('production_areas.production_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'name', 'number_locations'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No production areas found"),
                            'description' => $this->canEdit && $parent->stats->number_productions == 0 ? __('Get started by creating a production area. ✨')
                                : __("In fact, is no even create a production yet 🤷🏽‍♂️"),
                            'count'       => $parent->stats->number_production_areas,
                            'action'      => $this->canEdit && $parent->stats->number_productions == 0 ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new production'),
                                'label'   => __('production'),
                                'route'   => [
                                    'name'       => 'grp.org.productions.create',
                                    'parameters' => [$parent->slug]
                                ]
                            ] : null
                        ],
                        'Production' => [
                            'title'       => __("No production areas found"),
                            'description' => $this->canEdit ? __('Get started by creating a new production area. ✨')
                                : null,
                            'count'       => $parent->stats->number_production_areas,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new production area'),
                                'label'   => __('production area'),
                                'route'   => [
                                    'name'       => 'grp.org.productions.show.infrastructure.production-areas.create',
                                    'parameters' => [
                                        $parent->organisation->slug,
                                        $parent->slug
                                    ]
                                ]
                            ] : null
                        ],
                        default => null
                    }
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_locations', label: __('locations'), canBeHidden: false, sortable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $productionAreas): AnonymousResourceCollection
    {
        return ProductionAreaResource::collection($productionAreas);
    }

    public function htmlResponse(LengthAwarePaginator $productionAreas, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $container = null;
        if (class_basename($scope) == 'Production') {
            $container = [
                'icon'    => ['fal', 'fa-production'],
                'tooltip' => __('Production'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'Org/Production/ProductionAreas',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('production areas'),
                'pageHead'    => [
                    'title'     => __('production areas'),
                    'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-map-signs'],
                        'title' => __('production areas')
                    ],
                    'actions'   => [
                        $this->canEdit && $request->route()->getName() == 'grp.org.productions.show.infrastructure.production-areas.index' ? [
                            'type'   => 'buttonGroup',
                            'key'    => 'upload-add',
                            'button' => [
                                [
                                    'type'  => 'button',
                                    'style' => 'primary',
                                    'icon'  => ['fal', 'fa-upload'],
                                    'label' => 'upload',
                                    'route' => [
                                        'name'       => 'grp.models.production.production-areas.upload',
                                        'parameters' => [
                                            $this->parent->id
                                        ]
                                    ]
                                ],
                                [

                                    'type'  => 'button',
                                    'style' => 'create',
                                    'label' => __('areas'),
                                    'route' => [
                                        'name'       => 'grp.org.productions.show.infrastructure.production-areas.create',
                                        'parameters' => $request->route()->originalParameters()
                                    ]

                                ]
                            ]
                        ] : null,
                    ]
                ],
                'data'        => ProductionAreaResource::collection($productionAreas)
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('production areas'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.production-areas.index' =>
            array_merge(
                (new ShowInventoryDashboard())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name' => 'grp.oms.production-areas.index',
                        null
                    ]
                )
            ),
            'grp.org.productions.show.infrastructure.production-areas.index',
            =>
            array_merge(
                ShowProduction::make()->getBreadcrumbs($routeParameters),
                $headCrumb([
                    'name'       => 'grp.org.productions.show.infrastructure.production-areas.index',
                    'parameters' =>
                        [
                            $routeParameters['organisation'],
                            Production::where('slug', $routeParameters['production'])->first()->slug
                        ]
                ])
            ),
            default => []
        };
    }
}
