<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 May 2024 11:17:42 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\ManufactureTask\UI;

use App\Actions\Manufacturing\Production\UI\ShowProductionCrafts;
use App\Actions\OrgAction;
use App\Enums\UI\Manufacturing\ManufactureTasksTabsEnum;
use App\Http\Resources\Manufacturing\ManufactureTasksResource;
use App\InertiaTable\InertiaTable;
use App\Models\Manufacturing\ManufactureTask;
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

class IndexManufactureTasks extends OrgAction
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
        $this->initialisation($organisation, $request)->withTab(ManufactureTasksTabsEnum::values());

        return $this->handle($organisation);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $production;
        $this->initialisationFromProduction($production, $request)->withTab(ManufactureTasksTabsEnum::values());

        return $this->handle(parent: $production, prefix: ManufactureTasksTabsEnum::MANUFACTURE_TASKS->value);
    }

    public function handle(Production|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('manufacture_tasks.code', $value)
                    ->whereWith('manufacture_tasks.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(ManufactureTask::class);

        if ($parent instanceof Organisation) {
            $queryBuilder->where('manufacture_tasks.organisation_id', $parent->id);
        } else {
            $queryBuilder->where('manufacture_tasks.production_id', $parent->id);
        }


        return $queryBuilder
            ->defaultSort('manufacture_tasks.code')
            ->select(
                [
                    'manufacture_tasks.code',
                    'manufacture_tasks.id',
                    'manufacture_tasks.name',
                    'productions.slug as production_slug',
                    'manufacture_tasks.slug'
                ]
            )
            ->leftJoin('manufacture_task_stats', 'manufacture_task_stats.manufacture_task_id', 'manufacture_tasks.id')
            ->leftJoin('productions', 'manufacture_tasks.production_id', 'productions.id')
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
                            'title'  => __("No manufacture tasks found"),
                            'count'  => $parent->manufactureStats->number_manufacture_tasks,
                            'action' => null
                        ],
                        'Production' => [
                            'title'       => __("No manufacture tasks found"),
                            'description' => $this->canEdit ? __('Get started by creating your first manufacture task. ✨')
                                : null,
                            'count'       => $parent->stats->number_manufacture_tasks,
                            'action'      => $canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new manufacture task'),
                                'label'   => __('manufacture task'),
                                'route'   => [
                                    'name'       => 'grp.org.productions.show.crafts.manufacture_tasks.create',
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

    public function jsonResponse(LengthAwarePaginator $manufactureTasks): AnonymousResourceCollection
    {
        return ManufactureTasksResource::collection($manufactureTasks);
    }

    public function htmlResponse(LengthAwarePaginator $manufactureTasks, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Manufacturing/ManufactureTasks',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Manufacture tasks'),
                'pageHead'    => [
                    'title'     => __('Manufacture tasks'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-code-merge'],
                        'title' => __('manufacture_tasks'),
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
                                        'name'       => 'grp.models.production.manufacture_tasks.upload',
                                        'parameters' => [
                                            $this->parent->id
                                        ]
                                    ]
                                ],
                                [

                                    'type'  => 'button',
                                    'style' => 'create',
                                    'label' => __('task'),
                                    'route' => [
                                        'name'       => 'grp.org.productions.show.crafts.manufacture_tasks.create',
                                        'parameters' => $request->route()->originalParameters()
                                    ]

                                ]
                            ]
                        ] : null,
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => ManufactureTasksTabsEnum::navigation(),
                ],

                ManufactureTasksTabsEnum::MANUFACTURE_TASKS->value => $this->tab == ManufactureTasksTabsEnum::MANUFACTURE_TASKS->value ?
                    fn () => ManufactureTasksResource::collection($manufactureTasks)
                    : Inertia::lazy(fn () => ManufactureTasksResource::collection($manufactureTasks)),

            ]
        )->table(
            $this->tableStructure(
                parent: $this->parent,
                prefix: ManufactureTasksTabsEnum::MANUFACTURE_TASKS->value
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
                            'name'       => 'grp.org.productions.show.crafts.manufacture_tasks.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('Manufacture tasks'),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }


}
