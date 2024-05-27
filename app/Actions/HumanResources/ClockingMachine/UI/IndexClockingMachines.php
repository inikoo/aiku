<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 08:39:46 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\ClockingMachine\UI;

use App\Actions\HumanResources\WithWorkplaceSubNavigation;
use App\Actions\HumanResources\Workplace\UI\ShowWorkplace;
use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Http\Resources\HumanResources\ClockingMachinesResource;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexClockingMachines extends OrgAction
{
    use WithWorkplaceSubNavigation;


    private Organisation|Workplace $parent;

    public function handle(Workplace|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->whereStartWith('clocking_machines.name', $value);
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(ClockingMachine::class);

        if ($parent instanceof Organisation) {
            $query->where('clocking_machines.workplace_id', $parent->id);
        } else {
            $query->where('clocking_machines.organisation_id', $parent->id);
        }

        return $query->defaultSort('name')
            ->select([
                'clocking_machines.type',
                'clocking_machines.name',
                'clocking_machines.id',
                'clocking_machines.slug',
                'workplaces.name as workplace_name',
                'workplaces.slug as workplace_slug',

            ])
            ->leftJoin('workplaces', 'clocking_machines.workplace_id', '=', 'workplaces.id')
            ->allowedSorts(['name', 'type', 'workplace_name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Workplace $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    [
                        'title'       => __('no clocking machines'),
                        'description' => __('Get started by creating a new clocking machine.'),
                        'count'       => class_basename($parent == 'Organisation') ? $parent->humanResourcesStats->number_clocking_machines : $parent->stats->number_clocking_machines,

                        'action' => [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new clocking machine'),
                            'label'   => __('clocking machine'),
                            'route'   => [
                                'name'       => 'grp.org.hr.workplaces.show.clocking_machines.create',
                                'parameters' => request()->route()->originalParameters()
                            ]
                        ]

                    ]
                )
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true);
            if ($parent instanceof Organisation) {
                $table->column(key: 'workplace_name', label: __('workplace'), canBeHidden: false, sortable: true, searchable: true);
            }

            $table->defaultSort('name');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function asController(Organisation $organisation, Workplace $workplace, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $workplace;
        $this->initialisation($organisation, $request);

        return $this->handle($workplace);
    }

    public function jsonResponse(LengthAwarePaginator $clockingMachine): AnonymousResourceCollection
    {
        return ClockingMachinesResource::collection($clockingMachine);
    }

    public function htmlResponse(LengthAwarePaginator $clockingMachines, ActionRequest $request): Response
    {
        $actions = null;
        if ($this->canEdit) {
            $actions = [
                match ($request->route()->getName()) {
                    'grp.org.hr.workplaces.show.clocking_machines.index' => [
                        'type'  => 'button',
                        'style' => 'create',
                        'label' => __('clocking machine'),
                        'route' => [
                            'name'       => 'grp.org.hr.workplaces.show.clocking_machines.create',
                            'parameters' => $request->route()->originalParameters()
                        ]
                    ],
                    default => [
                        'type'  => 'button',
                        'style' => 'create',
                        'label' => __('clocking machine'),
                        'route' => [
                            'name'       => 'grp.org.hr.clocking_machines.create',
                            'parameters' => [$this->organisation->slug]
                        ]
                    ]
                }
            ];
        }


        return Inertia::render(
            'Org/HumanResources/ClockingMachines',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('clocking machines'),
                'pageHead'    => [
                    'title'         => __('Clocking machines'),
                    'actions'       => $actions,
                    'subNavigation' => $this->parent instanceof Workplace ? $this->getWorkplaceSubNavigation($this->parent) : null
                ],
                'data'        => ClockingMachinesResource::collection($clockingMachines)

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
                        'label' => __('Clocking machines'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.hr.clocking_machines.index' =>
            array_merge(
                ShowHumanResourcesDashboard::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.hr.clocking_machines.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'grp.org.hr.workplaces.show.clocking_machines.index',
            =>
            array_merge(
                ShowWorkplace::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb([
                    'name'       => 'grp.org.hr.workplaces.show.clocking_machines.index',
                    'parameters' =>
                        [
                            $routeParameters['organisation'],
                            $routeParameters['workplace']
                        ]
                ])
            ),
            default => []
        };
    }
}
