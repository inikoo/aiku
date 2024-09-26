<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 09:57:32 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet\UI;

use App\Actions\HumanResources\Employee\UI\ShowEmployee;
use App\Actions\HumanResources\WithEmployeeSubNavigation;
use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\Helpers\Period\PeriodEnum;
use App\Enums\UI\HumanResources\TimesheetsTabsEnum;
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
    use WithEmployeeSubNavigation;

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
            ->allowedSorts(['date', 'subject_name', 'working_duration', 'breaks_duration'])
            ->allowedFilters([$globalSearch, 'subject_name'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    protected function getPeriodFilters(): array
    {
        $elements = array_merge_recursive(
            PeriodEnum::labels(),
            PeriodEnum::date()
        );

        return [
            'period' => [
                'label'    => __('Period'),
                'elements' => $elements
            ],
        ];
    }

    public function tableStructure(Organisation|Employee|Guest $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $noResults = __("No timesheets found");
            if ($parent instanceof Employee) {
                $stats     = $parent->stats;
                $noResults = __("Employee has no timesheets");
            } else {
                $stats = $parent->humanResourcesStats;
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => $noResults,
                        'count' => $stats->number_timesheets
                    ]
                )
                ->withModelOperations($modelOperations)
                ->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true);

            if ($parent instanceof Organisation) {
                $table->column(key: 'subject_name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
            }

            foreach ($this->getPeriodFilters() as $periodFilter) {
                $table->periodFilters($periodFilter['elements']);
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
        $subNavigation = [];
        $model         = '';
        $title         = __('Timesheets');
        $icon          = [
            'title' => __('Timesheets'),
            'icon'  => 'fal fa-stopwatch'
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
            'Org/HumanResources/Timesheets',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $this->parent,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('timesheets'),
                'pageHead'    => [
                    'title'         => $title,
                    'icon'          => $icon,
                    'model'         => $model,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'subNavigation' => $subNavigation,
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => TimesheetsTabsEnum::navigation()
                ],

                'data' => TimesheetsResource::collection($timesheets)

            ]
        )->table(
            $this->tableStructure($this->parent, modelOperations: [

                'createLink' => [
                    [
                        'route' => [
                            'name'       => 'grp.org.hr.timesheets.index',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                        'label' => __('per employee')
                    ]
                ]

            ])
        );
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


    public function getBreadcrumbs(Organisation|Employee|Guest $parent, string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Timesheets'),
                        'icon'  => 'fal fa-bars',
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.hr.timesheets.index' => array_merge(
                ShowHumanResourcesDashboard::make()->getBreadcrumbs(Arr::only($routeParameters, 'organisation')),
                $headCrumb(
                    [
                        'name'       => 'grp.org.hr.timesheets.index',
                        'parameters' => Arr::only($routeParameters, 'organisation')
                    ]
                )
            ),
            'grp.org.hr.employees.show.timesheets.index' => array_merge(
                ShowEmployee::make()->getBreadcrumbs($parent, $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.hr.employees.show.timesheets.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
        };
    }
}
