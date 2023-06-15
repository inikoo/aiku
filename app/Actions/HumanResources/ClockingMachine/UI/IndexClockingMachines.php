<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:12:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\ClockingMachine\UI;

use App\Actions\HumanResources\WorkingPlace\UI\ShowWorkingPlace;
use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexClockingMachines extends InertiaAction
{
    public function handle(Workplace|Tenant $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('clocking_machines.slug', 'ILIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::CLOCKING_MACHINES->value);

        return QueryBuilder::for(ClockingMachine::class)
            ->defaultSort('clocking_machines.slug')
            ->select(
                [
                    'clocking_machines.id',
                    'clocking_machines.code',
                    'clocking_machines.slug',
                    'workplaces.slug as workplace_slug'
                ]
            )
            ->leftJoin('workplaces', 'clocking_machines.workplace_id', 'workplaces.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Workplace') {
                    $query->where('clocking_machines.workplace_id', $parent->id);
                }
            })
            ->allowedSorts(['slug','code'])
            ->allowedFilters([$globalSearch], 'slug', 'code')
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::CLOCKING_MACHINES->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure(?array $modelOperations = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations) {
            $table
                ->name(TabsAbbreviationEnum::CLOCKING_MACHINES->value)
                ->pageName(TabsAbbreviationEnum::CLOCKING_MACHINES->value.'Page')
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.working-places.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('hr.view')
            );
    }

    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(app('currentTenant'));
    }

    public function inWorkplace(Workplace $workplace, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($workplace);
    }


    public function jsonResponse(LengthAwarePaginator $clockingMachines): AnonymousResourceCollection
    {
        return ClockingMachineResource::collection($clockingMachines);
    }


    public function htmlResponse(LengthAwarePaginator $clockingMachines, ActionRequest $request): Response
    {
        return Inertia::render(
            'HumanResources/ClockingMachines',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('clocking machines'),
                'pageHead'    => [
                    'title'  => __('clocking machines'),
                    'create' => $this->canEdit && $this->routeName == 'hr.working-places.show.clocking-machines.index' ? [
                        'route' => [
                            'name'       => 'hr.working-places.show.clocking-machines.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('clocking machine')
                    ] : false,
                ],
                'data'        => ClockingMachineResource::collection($clockingMachines),
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
                        'label' => __('clocking machines'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'hr.clocking-machines.index' =>
            array_merge(
                (new HumanResourcesDashboard())->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'hr.clocking-machines.index',
                        null
                    ]
                )
            ),
            'hr.working-places.show.clocking-machines.index',
            =>
            array_merge(
                (new ShowWorkingPlace())->getBreadcrumbs(
                    $routeParameters['workplace']
                ),
                $headCrumb([
                    'name'       => 'hr.working-places.show.clocking-machines.index',
                    'parameters' =>
                        [
                            $routeParameters['workplace']->slug
                        ]
                ])
            ),
            default => []
        };
    }
}
