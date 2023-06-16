<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 11:34:32 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Clocking\UI;

use App\Actions\HumanResources\ClockingMachine\UI\ShowClockingMachine;
use App\Actions\HumanResources\WorkingPlace\UI\ShowWorkingPlace;
use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\HumanResources\ClockingResource;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexClockings extends InertiaAction
{
    public function handle($parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('clockings.slug', 'ILIKE', "%$value%")
                    ->orWhere('clockings.type', 'ILIKE', "%$value%");
            });
        });


        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::CLOCKINGS->value);

        return QueryBuilder::for(Clocking::class)
            ->defaultSort('clockings.slug')
            ->select(
                [
                    'clockings.id',
                    'clockings.type',
                    'clockings.slug',
                    'workplaces.slug as workplace_slug',
                    'clocking_machines.slug as clocking_machine_slug',
                    'clocking_machine_id'
                ]
            )
            ->leftJoin('workplaces', 'clockings.workplace_id', 'workplaces.id')
            ->leftJoin('clocking_machines', 'clockings.clocking_machine_id', 'clocking_machines.id')
            ->when($parent, function ($query) use ($parent) {
                switch (class_basename($parent)) {
                    case 'ClockingMachine':
                        $query->where('clockings.clocking_machine_id', $parent->id);
                        break;
                    case 'Workplace':
                        $query->where('clockings.workplace_id', $parent->id);
                        break;
                }
            })
            ->allowedSorts(['slug','type'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::CLOCKINGS->value.'Page'
            )
            ->withQueryString();
    }


    public function tableStructure(?array $modelOperations = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations) {
            $table
                ->name(TabsAbbreviationEnum::CLOCKINGS->value)
                ->pageName(TabsAbbreviationEnum::CLOCKINGS->value.'Page')
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: app('currentTenant'));
    }

    public function inWorkplace(Workplace $workplace, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: $workplace);
    }

    public function inClockingMachine(ClockingMachine $clockingMachine, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: $clockingMachine);
    }


    public function inWorkplaceInClockingMachine(Workplace $workplace, ClockingMachine $clockingMachine, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: $clockingMachine);
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.clockings.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('hr.view')
            );
    }

    public function jsonResponse(LengthAwarePaginator $clockings): AnonymousResourceCollection
    {
        return ClockingResource::collection($clockings);
    }


    public function htmlResponse(LengthAwarePaginator $clockings, ActionRequest $request): Response
    {
        return Inertia::render(
            'HumanResources/Clockings',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('Clockings'),
                'pageHead'    => [
                    'title'  => __('clockings'),
                    'create' => $this->canEdit
                    && (
                        $this->routeName == 'hr.working-places.show.clockings.index' or
                        $this->routeName == 'hr.working-places.show.clocking-machines.show.clockings.index'
                    )
                        ? [
                            'route' => match ($this->routeName) {
                                'hr.working-places.show.clockings.index' => [
                                    'name'       => 'hr.working-places.show.clockings.create',
                                    'parameters' => array_values($this->originalParameters)
                                ],
                                default => [
                                    'name'       => 'hr.working-places.show.clocking-machines.show.clockings.create',
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            },
                            'label' => __('clockings')
                        ] : false,
                ],
                'data'        => ClockingResource::collection($clockings),


            ]
        )->table($this->tableStructure());
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
            'hr.clockings.index' =>
            array_merge(
                (new HumanResourcesDashboard())->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'hr.clockings.index',
                        null
                    ]
                )
            ),
            'hr.working-places.show.clockings.index' =>
            array_merge(
                (new ShowWorkingPlace())->getBreadcrumbs($routeParameters['workplace']),
                $headCrumb([
                    'name'       => 'hr.working-places.show.clockings.index',
                    'parameters' =>
                        [
                            $routeParameters['workplace']->slug
                        ]
                ])
            ),
            'hr.clocking-machines.show.clockings.index' =>
            array_merge(
                (new ShowClockingMachine())->getBreadcrumbs(
                    'hr.clocking-machines.show',
                    [
                        'clockingMachine' => $routeParameters['clockingMachine']
                    ]
                ),
                $headCrumb([
                    'name'       => 'hr.clocking-machines.show.clockings.index',
                    'parameters' =>
                        [
                            $routeParameters['clockingMachine']->slug
                        ]
                ])
            ),
            'hr.working-places.show.clocking-machines.show.clockings.index' =>
            array_merge(
                (new ShowClockingMachine())->getBreadcrumbs(
                    'hr.working-places.show.clocking-machines.show',
                    [
                        'workplace'       => $routeParameters['workplace'],
                        'clockingMachine' => $routeParameters['clockingMachine']
                    ]
                ),
                $headCrumb([
                    'name'       => 'hr.working-places.show.clocking-machines.show.clockings.index',
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
