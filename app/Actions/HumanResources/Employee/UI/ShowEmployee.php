<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:13:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\HumanResources\JobPosition\UI\IndexJobPositions;
use App\Actions\HumanResources\Timesheet\UI\IndexTimesheets;
use App\Actions\HumanResources\WithEmployeeSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\UI\HumanResources\EmployeeTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Http\Resources\HumanResources\JobPositionsResource;
use App\Http\Resources\HumanResources\TimesheetsResource;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowEmployee extends OrgAction
{
    use WithActionButtons;
    use WithEmployeeSubNavigation;

    /**
     * @var array|\ArrayAccess|mixed
     */
    private Employee $employee;

    public function handle(Employee $employee): Employee
    {
        return $employee;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof ClockingMachine) {
            $employeeWorkplace = $this->employee->workplaces()
                    ->wherePivot('workplace_id', $request->user()->workplace_id)
                    ->count() > 0;

            return ($this->organisation->id === $request->user()->organisation_id)
                && $employeeWorkplace && $this->employee->state === EmployeeStateEnum::WORKING;
        }

        $this->canEdit   = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
        $this->canDelete = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, Employee $employee, ActionRequest $request): Employee
    {
        $this->initialisation($organisation, $request)->withTab(EmployeeTabsEnum::values());

        return $this->handle($employee);
    }

    public function htmlResponse(Employee $employee, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/HumanResources/Employee',
            [
                'title'       => __('employee'),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'navigation'  => [
                    'previous' => $this->getPrevious($employee, $request),
                    'next'     => $this->getNext($employee, $request),
                ],
                'pageHead'    => [
                    'icon'    => [
                        'title' => __('Employee'),
                        'icon'  => 'fal fa-user-hard-hat'
                    ],
                    'title'         => $employee->contact_name,
                    'subNavigation' => $this->getEmployeeSubNavigation($employee, $request),
                    'meta'          => [
                        [
                            'label'    => $employee->worker_number,
                            'leftIcon' => [
                                'icon'    => 'fal fa-id-card',
                                'tooltip' => __('Worker number')
                            ]
                        ],

                        [
                            'label'    => Arr::get(explode(':', $employee->pin), 1),
                            'leftIcon' => [
                                'icon'    => 'fal fa-key',
                                'tooltip' => __('Pin')
                            ]
                        ],

                        $employee->user ?
                            [
                                'label'    => $employee->user->username,
                                'leftIcon' => [
                                    'icon'    => 'fal fa-user',
                                    'tooltip' => __('User')
                                ]
                            ] : []
                    ],
                    'actions' => [
                        $this->canDelete ? $this->getDeleteActionIcon($request) : null,
                        $this->canEdit ? $this->getEditActionIcon($request) : null,
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => EmployeeTabsEnum::navigation()
                ],

                /*
                EmployeeTabsEnum::TIMESHEETS->value => $this->tab == EmployeeTabsEnum::TIMESHEETS->value ?
                    fn() => TimesheetsResource::collection(IndexTimesheets::run($employee, EmployeeTabsEnum::TIMESHEETS->value))
                    : Inertia::lazy(fn() => TimesheetsResource::collection(IndexTimesheets::run($employee, EmployeeTabsEnum::TIMESHEETS->value))),


                EmployeeTabsEnum::DATA->value => $this->tab == EmployeeTabsEnum::DATA->value ?
                    fn() => $this->getData($employee)
                    : Inertia::lazy(fn() => $this->getData($employee)),

                 EmployeeTabsEnum::JOB_POSITIONS->value => $this->tab == EmployeeTabsEnum::JOB_POSITIONS->value ?
                    fn() => JobPositionsResource::collection(IndexJobPositions::run($employee, EmployeeTabsEnum::JOB_POSITIONS->value, true))
                    : Inertia::lazy(fn() => JobPositionsResource::collection(IndexJobPositions::run($employee, EmployeeTabsEnum::JOB_POSITIONS->value, true))),


                */
                EmployeeTabsEnum::HISTORY->value => $this->tab == EmployeeTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($employee))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($employee))),


            ]
        )->table(IndexHistory::make()->tableStructure(prefix: EmployeeTabsEnum::HISTORY->value));
        /*
        ->table(IndexTimesheets::make()->tableStructure(
                parent: $employee,
                modelOperations: [
                    'createLink' => [
                        [
                            'type'   => 'button',
                            'style'  => 'primary',
                            'icon'   => 'fal fa-file-export',
                            'id'     => 'pdf-export',
                            'label'  => 'Excel',
                            'key'    => 'action',
                            'target' => '_blank',
                            'route'  => [
                                'name'       => 'grp.org.hr.employees.timesheets.export',
                                'parameters' => [
                                    'organisation' => $employee->organisation->slug,
                                    'employee'     => $employee->slug,
                                    'type'         => 'xlsx'
                                ]
                            ]
                        ]
                    ],
                ],
                prefix: EmployeeTabsEnum::TIMESHEETS->value
            ))
        ->table(IndexJobPositions::make()->tableStructure(prefix: EmployeeTabsEnum::JOB_POSITIONS->value));
        */
    }

    public function getData(Employee $employee): array
    {
        return Arr::except($employee->toArray(), ['id', 'source_id', 'working_hours', 'errors', 'salary', 'data']);
    }

    public function rules(): array
    {
        $rules = [];
        if (request()->user() instanceof ClockingMachine) {
            $rules = [
                'pin' => ['required', 'string', Rule::exists('employees', 'pin')]
            ];
        }

        return $rules;
    }

    public function inApi(ActionRequest $request): Employee
    {
        $workplace = $request->user()->workplace;

        $employee = $workplace
            ->employees()
            ->where('pin', $request->input('pin'))->firstOrFail();

        $this->employee = $employee;
        $this->initialisation($workplace->organisation, $request);

        return $this->handle($employee);
    }

    public function jsonResponse(Employee $employee): EmployeeResource
    {
        return new EmployeeResource($employee);
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $employee = Employee::where('slug', $routeParameters['employee'])->first();

        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.hr.employees.index',
                                'parameters' => array_merge(
                                    [
                                        '_query' => [
                                            'elements[state]' => 'working'
                                        ]
                                    ],
                                    Arr::only($routeParameters, 'organisation')
                                )
                            ],
                            'label' => __('employees')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.hr.employees.show',
                                'parameters' => $routeParameters
                            ],
                            'label' => $employee->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(Employee $employee, ActionRequest $request): ?array
    {
        $previous = Employee::where('slug', '<', $employee->slug)
            ->where('organisation_id', $this->organisation->id)
            ->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Employee $employee, ActionRequest $request): ?array
    {
        $next = Employee::where('slug', '>', $employee->slug)
            ->where('organisation_id', $this->organisation->id)
            ->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Employee $employee, string $routeName): ?array
    {
        if (!$employee) {
            return null;
        }

        return match ($routeName) {
            'grp.org.hr.employees.show' => [
                'label' => $employee->contact_name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'employee'     => $employee->slug
                    ]
                ]
            ]
        };
    }
}
