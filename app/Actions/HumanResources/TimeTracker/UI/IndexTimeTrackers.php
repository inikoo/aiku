<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 May 2024 20:26:25 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\TimeTracker\UI;

use App\Actions\HumanResources\ClockingMachine\UI\ShowClockingMachine;
use App\Actions\HumanResources\Workplace\UI\ShowWorkplace;
use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Http\Resources\HumanResources\ClockingsResource;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Timesheet;
use App\Models\HumanResources\TimeTracker;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use App\Services\QueryBuilder;

class IndexTimeTrackers extends OrgAction
{
    private Organisation|Workplace|ClockingMachine|Timesheet $parent;

    public function handle(Organisation|Workplace|ClockingMachine|Timesheet $parent, $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }
        $queryBuilder = QueryBuilder::for(TimeTracker::class);

        switch (class_basename($parent)) {
            case 'ClockingMachine':
                $queryBuilder->where('time_trackers.clocking_machine_id', $parent->id);
                break;
            case 'Workplace':
                $queryBuilder->where('time_trackers.workplace_id', $parent->id);
                break;
            case 'Organisation':
                $queryBuilder->where('time_trackers.organisation_id', $parent->id);
                break;
            case 'Timesheet':
                $queryBuilder->where('time_trackers.timesheet_id', $parent->id);
                break;
        }

        return $queryBuilder
            ->defaultSort('time_trackers.starts_at')
            ->select(
                [
                    'starts_at',
                    'ends_at',
                    'duration',
                    'time_trackers.id',
                    'status'
                ]
            )
            ->allowedSorts(['starts_at'])
            ->withPaginator($prefix)
            ->withQueryString();
    }


    public function tableStructure(Organisation|Workplace|ClockingMachine|Timesheet $parent, ?array $modelOperations = null, $prefix = null): Closure
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
                        'title'       => __('no clockings'),
                        'description' => $this->canEdit ? __('Get started by creating a new clocking.') : null,
                        'count'       =>
                            class_basename($parent) == 'ClockingMachine' ? $parent->humanResourcesStats?->number_clockings : $parent->stats?->number_clockings,
                    ]
                )
                ->column(key: 'starts_at', label: __('clocked in'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'ends_at', label: __('clocked out'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'status', label: 'status', type: 'icon')
                ->column(key: 'action', label: 'action', type: 'icon')
                ->defaultSort('starts_at');
        };
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle(parent: $organisation);
    }

    public function inWorkplace(Organisation $organisation, Workplace $workplace, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $workplace;
        $this->initialisation($organisation, $request);

        return $this->handle(parent: $workplace);
    }

    public function inClockingMachine(Organisation $organisation, ClockingMachine $clockingMachine, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $clockingMachine;
        $this->initialisation($organisation, $request);

        return $this->handle(parent: $clockingMachine);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplaceInClockingMachine(Organisation $organisation, Workplace $workplace, ClockingMachine $clockingMachine, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $clockingMachine;
        $this->initialisation($organisation, $request);

        return $this->handle(parent: $clockingMachine);
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
    }

    public function jsonResponse(LengthAwarePaginator $clockings): AnonymousResourceCollection
    {
        return ClockingsResource::collection($clockings);
    }


    public function htmlResponse(LengthAwarePaginator $clockings, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/HumanResources/TimeTrackers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Clockings'),
                'pageHead'    => [
                    'title'   => __('clockings'),
                    'actions' => [
                        $this->canEdit
                        && (
                            $request->route()->getName() == 'grp.org.hr.workplaces.show.clockings.index' or
                            $request->route()->getName() == 'grp.org.hr.workplaces.show.clocking_machines.show.clockings.index'
                        )
                            ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('clockings'),
                            'route' =>
                                match ($request->route()->getName()) {
                                    'grp.org.hr.workplaces.show.clockings.index' => [
                                        'name'       => 'grp.org.hr.workplaces.show.clockings.create',
                                        'parameters' => $request->route()->originalParameters()
                                    ],
                                    default => [
                                        'name'       => 'grp.org.hr.workplaces.show.clocking_machines.show.clockings.create',
                                        'parameters' => $request->route()->originalParameters()
                                    ]
                                }
                        ] : false
                    ]
                ],
                'data'        => ClockingsResource::collection($clockings),


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
                        'label' => __('clockings'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.hr.clockings.index' =>
            array_merge(
                (new ShowHumanResourcesDashboard())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name' => 'grp.org.hr.clockings.index',
                        null
                    ]
                )
            ),
            'grp.org.hr.workplaces.show.clockings.index' =>
            array_merge(
                (new ShowWorkplace())->getBreadcrumbs($routeParameters['workplace']),
                $headCrumb([
                    'name'       => 'grp.org.hr.workplaces.show.clockings.index',
                    'parameters' =>
                        [
                            $routeParameters['workplace']->slug
                        ]
                ])
            ),
            'grp.org.hr.clocking_machines.show.clockings.index' =>
            array_merge(
                (new ShowClockingMachine())->getBreadcrumbs(
                    'grp.org.hr.clocking_machines.show',
                    [
                        'clockingMachine' => $routeParameters['clockingMachine']
                    ]
                ),
                $headCrumb([
                    'name'       => 'grp.org.hr.clocking_machines.show.clockings.index',
                    'parameters' =>
                        [
                            $routeParameters['clockingMachine']->slug
                        ]
                ])
            ),
            'grp.org.hr.workplaces.show.clocking_machines.show.clockings.index' =>
            array_merge(
                (new ShowClockingMachine())->getBreadcrumbs(
                    'grp.org.hr.workplaces.show.clocking_machines.show',
                    [
                        'workplace'       => $routeParameters['workplace'],
                        'clockingMachine' => $routeParameters['clockingMachine']
                    ]
                ),
                $headCrumb([
                    'name'       => 'grp.org.hr.workplaces.show.clocking_machines.show.clockings.index',
                    'parameters' =>
                        [
                            $routeParameters['workplace']->slug,
                            $routeParameters['clockingMachine']->slug,

                        ]
                ])
            ),

            default => []
        };
    }
}
