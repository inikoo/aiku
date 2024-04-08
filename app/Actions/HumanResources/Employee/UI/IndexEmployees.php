<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:12:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\HumanResources\Employee\EmployeeTypeEnum;
use App\Http\Resources\HumanResources\EmployeeInertiaResource;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Group;
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

class IndexEmployees extends OrgAction
{
    private Organisation $parent;

    protected function getElementGroups(): array
    {
        return [
            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    EmployeeStateEnum::labels(),
                    EmployeeStateEnum::count($this->organisation)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
            'type'  => [
                'label'    => __('Type'),
                'elements' => EmployeeTypeEnum::labels(),
                'engine'   => function ($query, $elements) {
                    $query->whereIn('type', $elements);
                }
            ],
        ];
    }

    public function handle(Organisation|Group $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('employees.contact_name', $value)
                    ->orWhere('employees.slug', 'ILIKE', "$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $queryBuilder = QueryBuilder::for(Employee::class);

        if (class_basename($parent) == 'Organisation') {
            $queryBuilder->where('organisation_id', $parent->id);
        } elseif (class_basename($parent) == 'Group') {
            $queryBuilder->where('group_id', $parent->id);
        }


        foreach ($this->getElementGroups() as $key => $elementGroup) {

            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }


        return $queryBuilder
            ->defaultSort('employees.slug')
            ->select(['slug', 'id', 'job_title', 'contact_name', 'state'])
            ->with('jobPositions')
            ->allowedSorts(['slug', 'state', 'contact_name', 'job_title'])
            ->allowedFilters([$globalSearch, 'slug', 'contact_name', 'state'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Group $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups() as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }


            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch();

            if(class_basename($parent)=='Organisation') {
                $table->withEmptyState(
                    [
                        'title'       => __('no employees'),
                        'description' => $this->canEdit ? __('Get started by creating a new employee.') : null,
                        'count'       => $parent->humanResourcesStats->number_employees,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new employee'),
                            'label'   => __('employee'),
                            'route'   => [
                                'name'       => 'grp.org.hr.employees.create',
                                'parameters' => [
                                    'organisation' => $this->parent->slug
                                ]
                            ]
                        ] : null
                    ]
                );
            }
            $table->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'contact_name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'job_title', label: __('position'), canBeHidden: false)
                ->column(key: 'state', label: __('state'), canBeHidden: false)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if(class_basename($this->parent)=='Organisation') {
            $this->canEdit = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

            return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
        } else {
            return $request->user()->hasPermissionTo('group-reports');
        }
    }


    public function jsonResponse(LengthAwarePaginator $employees): AnonymousResourceCollection
    {
        return EmployeeResource::collection($employees);
    }


    public function htmlResponse(LengthAwarePaginator $employees, ActionRequest $request): Response
    {
        return Inertia::render(
            'HumanResources/Employees',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('employees'),
                'pageHead'    => [
                    'title'   => __('employees'),
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('employee'),
                            'route' => [
                                'name'       => 'grp.org.hr.employees.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false
                    ]
                ],
                'data'        => EmployeeInertiaResource::collection($employees),
            ]
        )->table($this->tableStructure($this->parent));
    }


    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }


    public function getBreadcrumbs($routeParameters): array
    {
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.hr.employees.index',
                            'parameters' => array_merge(
                                [
                                    '_query' => [
                                        'elements[state]' => 'working'
                                    ]
                                ],
                                $routeParameters
                            )
                        ],
                        'label' => __('employees'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
