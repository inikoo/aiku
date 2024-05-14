<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 09:33:11 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Http\Resources\HumanResources\JobPositionsResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexJobPositions extends OrgAction
{
    public function handle(Organisation|Employee $parent, string $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('job_positions.name', $value)
                    ->orWhereStartWith('job_positions.slug', $value);
            });
        });

        $queryBuilder = QueryBuilder::for(JobPosition::class);
        $queryBuilder->leftJoin('job_position_stats', 'job_positions.id', 'job_position_stats.job_position_id');

        // dd($parent);
        if ($parent instanceof Organisation) {
            $queryBuilder->where('organisation_id', $parent->id);

        } elseif ($parent instanceof Employee) {
            $queryBuilder->whereHas('employees', function ($query) use ($parent) {
                $query->where('job_positionable_id', $parent->id);
                $query->where('job_positionable_type', class_basename($parent));
            });
        }

        return $queryBuilder
            ->defaultSort('job_positions.code')
            ->select(['code', 'job_positions.slug', 'name', 'number_employees_currently_working'])
            ->allowedSorts(['slug', 'name', 'number_employees_currently_working'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("org-supervisor.{$this->organisation->id}.human-resources");
        return  $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
    }


    public function jsonResponse(LengthAwarePaginator $jobPositions): AnonymousResourceCollection
    {
        return JobPositionsResource::collection($jobPositions);
    }

    public function tableStructure(?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_employees_currently_working', label: __('employees'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function htmlResponse(LengthAwarePaginator $jobPositions, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/HumanResources/JobPositions',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('job positions'),
                'pageHead'    => [
                    'title'   => __('positions'),
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('job position'),
                            'route' => [
                                'name'       => 'grp.org.hr.job-positions.create',
                                'parameters' => [
                                    'organisation' => $this->organisation->slug
                                ]
                            ]
                        ] : false
                    ]
                ],
                'data'        => JobPositionsResource::collection($jobPositions),


            ]
        )->table($this->tableStructure());
    }


    public function asController(Organisation $organisation, ActionRequest $request, Employee $employee = null): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        $parent = $employee ?? $organisation;

        return $this->handle($parent);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.hr.job-positions.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('positions'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
