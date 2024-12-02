<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 09:33:11 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\HumanResources\Employee\UI\ShowEmployee;
use App\Actions\HumanResources\WithEmployeeSubNavigation;
use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Http\Resources\HumanResources\JobPositionsResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexJobPositions extends OrgAction
{
    use WithEmployeeSubNavigation;

    private Employee|Organisation $parent;

    public function handle(Organisation|Employee $parent, string $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('job_positions.name', $value)
                    ->orWhereStartWith('job_positions.code', $value);
            });
        });

        $queryBuilder = QueryBuilder::for(JobPosition::class);
        $queryBuilder->leftJoin('job_position_stats', 'job_positions.id', 'job_position_stats.job_position_id');
        $queryBuilder->select(['code', 'job_positions.slug', 'name', 'number_employees_currently_working']);
        if ($parent instanceof Organisation) {

            $queryBuilder->where(function (Builder $query) use ($parent) {
                $query->where('organisation_id', $parent->id)->orWhere('organisation_id', null);
            });


        } else {
            $queryBuilder->leftJoin('employee_has_job_positions', 'job_positions.id', 'employee_has_job_positions.job_position_id');
            $queryBuilder->where('employee_id', $parent->id);
            $queryBuilder->addSelect('employee_has_job_positions.share');
        }


        return $queryBuilder
            ->defaultSort('job_positions.code')
            ->allowedSorts(['code', 'name', 'number_employees_currently_working', 'share'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("org-supervisor.{$this->organisation->id}.human-resources");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
    }


    public function jsonResponse(LengthAwarePaginator $jobPositions): AnonymousResourceCollection
    {
        return JobPositionsResource::collection($jobPositions);
    }

    public function tableStructure(Organisation|Employee $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->withEmptyState(
                match (class_basename($parent)) {
                    'Organisation' => [
                        'title' => __("No responsibilities found"),
                        'count' => $parent->humanResourcesStats->number_job_positions,
                    ],
                    'Employee' => [
                        'title' => __("Employee has no responsibilities"),
                        'count' => $parent->stats->number_job_positions

                    ],
                    default => null
                }
            );

            $table->withLabelRecord([__('responsibility'), __('responsibilities')]);
            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);

            if ($parent instanceof Organisation) {
                $table->column(key: 'number_employees_currently_working', label: __('employees'), canBeHidden: false, sortable: true, searchable: true);

                //$table->column(key: 'department', label: __('department'), canBeHidden: false, sortable: true, searchable: true);
                //$table->column(key: 'team', label: __('team'), canBeHidden: false, sortable: true, searchable: true);
            } else {
                $table->column(key: 'share', label: __('Share'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->defaultSort('code');
        };
    }

    public function htmlResponse(LengthAwarePaginator $jobPositions, ActionRequest $request): Response
    {
        $subNavigation = [];
        $model         = '';
        $title         = __('Responsibilities');
        $icon          = [
            'title' => __('Responsibilities'),
            'icon'  => 'fal fa-clipboard-list-check'
        ];
        $afterTitle    = null;
        $iconRight     = null;

        if ($this->parent instanceof Employee) {
            $afterTitle    = [
                'label' => $title
            ];
            $iconRight     = $icon;
            $subNavigation = $this->getEmployeeSubNavigation($this->parent, $request);
            $title         = $this->parent->contact_name;

            $icon = [
                'icon'  => ['fal', 'fa-user-hard-hat'],
                'title' => __('employee')
            ];
        }


        return Inertia::render(
            'Org/HumanResources/JobPositions',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $this->parent,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Responsibilities'),
                'pageHead'    => [
                    'title'         => $title,
                    'icon'          => $icon,
                    'model'         => $model,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'subNavigation' => $subNavigation,
                ],
                'data'        => JobPositionsResource::collection($jobPositions),


            ]
        )->table($this->tableStructure($this->parent));
    }


    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }


    public function inEmployee(Organisation $organisation, Employee $employee, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $employee;
        $this->initialisation($organisation, $request);

        return $this->handle($employee);
    }

    public function getBreadcrumbs(Organisation|Employee $parent, string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Responsibilities'),
                        'icon'  => 'fal fa-bars',
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.hr.job_positions.index' => array_merge(
                ShowHumanResourcesDashboard::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.hr.job_positions.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'grp.org.hr.employees.show.positions.index' => array_merge(
                ShowEmployee::make()->getBreadcrumbs($parent, $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.hr.employees.show.positions.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
        };
    }
}
