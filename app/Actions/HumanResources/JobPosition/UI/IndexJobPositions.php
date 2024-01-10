<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 09:33:11 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\InertiaOrganisationAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexJobPositions extends InertiaOrganisationAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(string $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('job_positions.name', $value)
                    ->orWhere('job_positions.slug', 'ILIKE', "$value%");
            });
        });

        $queryBuilder=QueryBuilder::for(JobPosition::class);

        return $queryBuilder
            ->defaultSort('job_positions.slug')
            ->select(['slug', 'id', 'name', 'number_employees'])
            ->allowedSorts(['slug', 'name', 'number_employees'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.edit");
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.view")
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return JobPositionResource::collection($this->handle());
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
                ->withEmptyState(
                    [
                        'title'       => __('no job positions'),
                        'description' => $this->canEdit ? __('Get started by creating a new job position.') : null,
                        'count'       => app('currentTenant')->stats->number_job_position,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new job position'),
                            'label'   => __('job position'),
                            'route'   => [
                                'name'       => 'grp.org.hr.job-positions.create',
                                'parameters' => [
                                    'organisation' => $this->organisation->slug
                                ]
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_employees', label: __('employees'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function htmlResponse(LengthAwarePaginator $jobPositions): Response
    {
        return Inertia::render(
            'HumanResources/JobPositions',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('job positions'),
                'pageHead'    => [
                    'title'  => __('positions'),
                    'actions'=> [
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
                'data'        => JobPositionResource::collection($jobPositions),


            ]
        )->table($this->tableStructure());
    }


    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $this->perPage = 100;

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.hr.job-positions.index',
                            'parameters' => [
                                'organisation' => $this->organisation->slug
                            ]
                        ],
                        'label' => __('positions'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
