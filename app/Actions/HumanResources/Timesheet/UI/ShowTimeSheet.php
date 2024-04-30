<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 09:57:32 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet\UI;

use App\Actions\OrgAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\UI\EmployeeTabsEnum;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowTimeSheet extends OrgAction
{
    public function handle(Employee $employee): Employee
    {
        return $employee;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function asController(Organisation $organisation, Employee $employee, ActionRequest $request): Employee
    {
        $this->initialisation($organisation, $request)->withTab(EmployeeTabsEnum::values());
        return $this->handle($employee);
    }

    public function htmlResponse(Employee $employee, ActionRequest $request): Response
    {
        return Inertia::render(
            'HumanResources/TimeSheet',
            [
                'title'                                 => __('employee'),
                'breadcrumbs'                           => $this->getBreadcrumbs($employee),
                'navigation'                            => [
                    'previous' => $this->getPrevious($employee, $request),
                    'next'     => $this->getNext($employee, $request),
                ],
                'pageHead'    => [
                    'title' => $employee->slug,
                    'meta'  => [
                        [
                            'name'     => $employee->worker_number,
                            'leftIcon' => [
                                'icon'    => 'fal fa-id-card',
                                'tooltip' => __('Worker number')
                            ]
                        ],

                        $employee->user ?
                            [
                                'name'     => $employee->user->username,
                                'leftIcon' => [
                                    'icon'    => 'fal fa-user',
                                    'tooltip' => __('User')
                                ]
                            ] : []
                    ],
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => $request->route()->originalParameters()
                        ]
                    ] : false,
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => EmployeeTabsEnum::navigation()
                ]
            ]
        );
    }


    public function jsonResponse(Employee $employee): EmployeeResource
    {
        return new EmployeeResource($employee);
    }

    public function getBreadcrumbs(Employee $employee, $suffix = null): array
    {
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs([
                'organisation' => $this->organisation->slug
            ]),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.hr.employees.index',
                                'parameters' => [
                                    'organisation' => $this->organisation->slug
                                ]
                            ],
                            'label' => __('employees')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.hr.employees.show',
                                'parameters' => [
                                    'organisation' => $this->organisation->slug,
                                    'employee'     => $employee->slug
                                ]
                            ],
                            'label' => $employee->worker_number,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(Employee $employee, ActionRequest $request): ?array
    {
        $previous = Employee::where('slug', '<', $employee->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Employee $employee, ActionRequest $request): ?array
    {
        $next = Employee::where('slug', '>', $employee->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Employee $employee, string $routeName): ?array
    {
        if(!$employee) {
            return null;
        }
        return match ($routeName) {
            'grp.org.hr.employees.show'=> [
                'label'=> $employee->contact_name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'=> $this->organisation->slug,
                        'employee'    => $employee->slug
                    ]
                ]
            ]
        };
    }
}
