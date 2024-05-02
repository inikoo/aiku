<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 09:57:32 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet\UI;

use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Http\Resources\HumanResources\EmployeeInertiaResource;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Timesheet;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexTimesheets extends OrgAction
{
    public function handle(Employee|Guest $subject, $prefix = null, $isTodayTimesheet = false): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('timesheets.slug', $value);
            });
        });

        $query = QueryBuilder::for(Timesheet::class);

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        if($subject instanceof Employee) {
            $query->where([['subject_type', class_basename($subject)], ['subject_id', $subject->id]]);
        }

        if($isTodayTimesheet) {
            $query->whereDate('date', now()->format('Y-m-d'));
        }

        return $query
            ->defaultSort('slug')
            ->with(['organisation', 'subject'])
            ->allowedSorts(['slug'])
            ->allowedFilters([$globalSearch, 'slug'])
            ->withPaginator($prefix)
            ->withQueryString();
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
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'start_at', label: __('start at'), canBeHidden: false)
                ->column(key: 'end_at', label: __('end at'), canBeHidden: false)
                ->column(key: 'working_duration', label: __('working duration'), canBeHidden: false)
                ->column(key: 'breaks_duration', label: __('breaks duration'), canBeHidden: false)
                ->column(key: 'number_time_trackers', label: __('time tracker'), canBeHidden: false)
                ->column(key: 'number_open_time_trackers', label: __('open time tracker'), canBeHidden: false)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit")
            );
    }


    public function jsonResponse(LengthAwarePaginator $employees): AnonymousResourceCollection
    {
        return EmployeeResource::collection($employees);
    }


    public function htmlResponse(LengthAwarePaginator $employees): Response
    {
        return Inertia::render(
            'HumanResources/Employees',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('employees'),
                'pageHead'    => [
                    'title'  => __('employees'),
                    'create' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.org.hr.employees.create',
                            'parameters' => [
                                'organisation' => $this->organisation->slug
                            ]
                        ],
                        'label' => __('employee')
                    ] : false,
                ],
                'data'        => EmployeeInertiaResource::collection($employees),
            ]
        )->table($this->tableStructure());
    }


    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        return $this->handle();
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs([
                'organisation' => $this->organisation->slug
            ]),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.hr.employees.index',
                            'parameters' => [
                                'organisation' => $this->organisation->slug
                            ]
                        ],
                        'label' => __('employees'),
                        'icon'  => 'fal fa-bars',
                    ],
                ]
            ]
        );
    }
}
