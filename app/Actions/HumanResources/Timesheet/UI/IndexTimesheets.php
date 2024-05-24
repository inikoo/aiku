<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 09:57:32 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet\UI;

use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Http\Resources\HumanResources\TimesheetsResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Timesheet;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexTimesheets extends OrgAction
{
    private Employee|Organisation|Guest $parent;

    public function handle(Organisation|Employee|Guest $parent, ?string $prefix = null, bool $isTodayTimesheet = false): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('timesheets.subject_name', $value);
            });
        });

        $query = QueryBuilder::for(Timesheet::class);

        if ($parent instanceof Organisation) {
            $query->where('organisation_id', $parent->id);
        } elseif ($parent instanceof Employee) {
            $query->where('subject_type', 'Employee')
                ->where('subject_id', $parent->id);
        } else {
            $query->where('subject_type', 'Guest')->where('subject_id', $parent->id);
        }

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        if ($isTodayTimesheet) {
            $query->whereDate('date', now()->format('Y-m-d'));
        }

        $query->withFilterPeriod('created_at');

        return $query
            ->defaultSort('date')
            ->allowedSorts(['date', 'subject_name','working_duration','breaks_duration'])
            ->allowedFilters([$globalSearch, 'subject_name'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Employee|Guest $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true);

            if ($parent instanceof Organisation) {
                $table->column(key: 'subject_name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
            }


            $table->column(key: 'working_duration', label: __('working'), canBeHidden: false, sortable: true)
                ->column(key: 'breaks_duration', label: __('breaks'), canBeHidden: false, sortable: true)
             //   ->column(key: 'number_time_trackers', label: __('time tracker'), canBeHidden: false)
              //  ->column(key: 'number_open_time_trackers', label: __('open time tracker'), canBeHidden: false)
                ->defaultSort('date');
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


    public function jsonResponse(LengthAwarePaginator $timesheets): AnonymousResourceCollection
    {
        return TimesheetsResource::collection($timesheets);
    }


    public function htmlResponse(LengthAwarePaginator $timesheets, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/HumanResources/Timesheets',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('timesheets'),
                'pageHead'    => [
                    'title'  => __('timesheets'),
                    'create' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.org.hr.employees.create',
                            'parameters' => [
                                'organisation' => $this->organisation->slug
                            ]
                        ],
                        'label' => __('timesheets')
                    ] : false,
                ],
                'data'        => TimesheetsResource::collection($timesheets),
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

    public function inGuest(Organisation $organisation, Guest $guest, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $guest;
        $this->initialisation($organisation, $request);

        return $this->handle($guest);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            ShowHumanResourcesDashboard::make()->getBreadcrumbs(
                Arr::only($routeParameters, 'organisation')
            ),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.hr.timesheets.index',
                            'parameters' => Arr::only($routeParameters, 'organisation')
                        ],
                        'label' => __('timesheets'),
                        'icon'  => 'fal fa-bars',
                    ],
                ]
            ]
        );
    }
}
