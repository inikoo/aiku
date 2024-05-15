<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:46 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Manufacturing\Artefact\UI;

use App\Actions\Manufacturing\Production\UI\ShowProductionCrafts;
use App\Actions\OrgAction;
use App\Enums\UI\Manufacturing\ArtefactsTabsEnum;
use App\Http\Resources\Manufacturing\ArtefactsResource;
use App\Http\Resources\Manufacturing\ProductionsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Manufacturing\Artefact;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexArtefacts extends OrgAction
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

        $this->canEdit = $request->user()->hasPermissionTo("productions_rd.{$this->production->id}.edit");

        return $request->user()->hasPermissionTo("productions_rd.{$this->production->id}.view");
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(ArtefactsTabsEnum::values());

        return $this->handle($organisation);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $production;
        $this->initialisationFromProduction($production, $request)->withTab(ArtefactsTabsEnum::values());

        return $this->handle(parent: $production, prefix: ArtefactsTabsEnum::ARTEFACTS->value);
    }

    public function handle(Production|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('artefacts.code', $value)
                    ->whereWith('artefacts.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Artefact::class);

        if ($parent instanceof Organisation) {
            $queryBuilder->where('artefacts.organisation_id', $parent->id);
        } else {
            $queryBuilder->where('artefacts.production_id', $parent->id);
        }


        return $queryBuilder
            ->defaultSort('artefacts.code')
            ->select(
                [
                    'artefacts.code',
                    'artefacts.id',
                    'artefacts.name',
                    'productions.slug as production_slug',
                    'artefacts.slug'
                ]
            )
            ->leftJoin('artefact_stats', 'artefact_stats.artefact_id', 'artefacts.id')
            ->leftJoin('productions', 'artefacts.production_id', 'productions.id')
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Production|Organisation $parent, ?array $modelOperations = null, $prefix = null, bool $canEdit = false): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
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
                            'title'  => __("No artefacts found"),
                            'count'  => $parent->manufactureStats->number_artefacts,
                            'action' => null
                        ],
                        'Production' => [
                            'title'       => __("No artefacts found"),
                            'description' => $this->canEdit ? __('Get started by creating your first artefact. ✨')
                                : null,
                            'count'       => $parent->stats->number_artefacts,
                            'action'      => $canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new artefact'),
                                'label'   => __('artefact'),
                                'route'   => [
                                    'name'       => 'grp.org.productions.show.crafts.artefacts.create',
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
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $artefacts): AnonymousResourceCollection
    {
        return ArtefactsResource::collection($artefacts);
    }

    public function htmlResponse(LengthAwarePaginator $artefacts, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Manufacturing/Artefacts',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('artefacts'),
                'pageHead'    => [
                    'title'     => __('artefacts'),
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-hamsa'],
                        'title' => __('artefacts'),
                    ],
                    'actions'   => [
                        $this->canEdit && $this->parent instanceof Production ? [
                            'type'   => 'buttonGroup',
                            'key'    => 'upload-add',
                            'button' => [
                                [
                                    'type'  => 'button',
                                    'style' => 'primary',
                                    'icon'  => ['fal', 'fa-upload'],
                                    'label' => 'upload',
                                    'route' => [
                                        'name'       => 'grp.models.production.artefacts.upload',
                                        'parameters' => [
                                            $this->parent->id
                                        ]
                                    ]
                                ],
                                [

                                    'type'  => 'button',
                                    'style' => 'create',
                                    'label' => __('artefact'),
                                    'route' => [
                                        'name'       => 'grp.org.productions.show.crafts.artefacts.create',
                                        'parameters' => $request->route()->originalParameters()
                                    ]

                                ]
                            ]
                        ] : null,
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => ArtefactsTabsEnum::navigation(),
                ],

                ArtefactsTabsEnum::ARTEFACTS->value => $this->tab == ArtefactsTabsEnum::ARTEFACTS->value ?
                    fn () => ProductionsResource::collection($artefacts)
                    : Inertia::lazy(fn () => ProductionsResource::collection($artefacts)),

            ]
        )->table(
            $this->tableStructure(
                parent: $this->parent,
                prefix: ArtefactsTabsEnum::ARTEFACTS->value
            )
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        return array_merge(
            ShowProductionCrafts::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.productions.show.crafts.artefacts.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('artefacts'),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }


}
