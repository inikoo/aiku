<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:12:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\HumanResources\EmployeeInertiaResource;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexEmployees extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            // $stateArray = array();
            // if (str_contains($value, '|')) {
            //     $stateArray = explode('|',$value);
            //     $stateArray = array_filter($stateArray, fn($val) => !is_null($val) && $val !== '');
            // }
            // $stateArray
            $query->where(function ($query) use ($value) {
                $query->where('employees.name', 'LIKE', "%$value%")
                    ->orWhere('employees.slug', 'LIKE', "%$value%");
                    // ->orWhereIn('employees.state', $stateArray);
            });
        });

        $filterCheck = AllowedFilter::callback('check', function ($query, $values) {
            $query->where(function ($query) use ($values) {
                $query->whereIn('employees.state', $values);
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::EMPLOYEES->value);

        return QueryBuilder::for(Employee::class)
            ->defaultSort('employees.slug')
            ->select(['slug', 'id', 'worker_number', 'name','state'])
            ->with('jobPositions')
            ->allowedSorts(['slug', 'worker_number', 'name'])
            ->allowedFilters([$globalSearch , $filterCheck])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::EMPLOYEES->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::EMPLOYEES->value)
                ->pageName(TabsAbbreviationEnum::EMPLOYEES->value.'Page');
            $table
                ->withGlobalSearch()
                // ->withFilterCheck()
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'job_positions', label: __('position'), canBeHidden: false, sortable: false, searchable: false)
                ->column(key: 'state', label: __('state'), canBeHidden: false, sortable: false, searchable: false)
                ->column(key: 'actions', label: __('actions'))
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('hr.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $employees): AnonymousResourceCollection
    {
        return EmployeeResource::collection($employees);
    }


    public function htmlResponse(LengthAwarePaginator $employees, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'HumanResources/Employees',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('employees'),
                'pageHead'    => [
                    'title'  => __('employees'),
                    'create' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'hr.employees.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('employee')
                    ] : false,
                ],
                'data'        => EmployeeInertiaResource::collection($employees),
            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle();
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            (new HumanResourcesDashboard())->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'hr.employees.index'
                        ],
                        'label' => __('employees'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
