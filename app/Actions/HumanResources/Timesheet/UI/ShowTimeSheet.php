<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 09:57:32 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet\UI;

use App\Actions\HumanResources\Employee\UI\ShowEmployee;
use App\Actions\OrgAction;
use App\Enums\UI\HumanResources\EmployeeTabsEnum;
use App\Http\Resources\HumanResources\TimesheetsResource;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Timesheet;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowTimeSheet extends OrgAction
{
    public function handle(Timesheet $timesheet): Timesheet
    {
        return $timesheet;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function asController(Organisation $organisation, Timesheet $timesheet, ActionRequest $request): Timesheet
    {
        $this->initialisation($organisation, $request)->withTab(EmployeeTabsEnum::values());

        return $this->handle($timesheet);
    }

    public function inEmployee(Organisation $organisation, Employee $employee, Timesheet $timesheet, ActionRequest $request): Timesheet
    {
        $this->initialisation($organisation, $request)->withTab(EmployeeTabsEnum::values());

        return $this->handle($timesheet);
    }

    public function htmlResponse(Timesheet $timesheet, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/HumanResources/TimeSheet',
            [
                'title'                                 => __('timesheet'),
                'breadcrumbs'                           => $this->getBreadcrumbs($timesheet),
                'navigation'                            => [
                    'previous' => $this->getPrevious($timesheet, $request),
                    'next'     => $this->getNext($timesheet, $request),
                ],
                'pageHead'    => [
                    'title' => $timesheet->slug,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => $request->route()->originalParameters()
                        ]
                    ] : false,
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => []
                ]
            ]
        );
    }


    public function jsonResponse(Timesheet $timesheet): TimesheetsResource
    {
        return new TimesheetsResource($timesheet);
    }

    public function getBreadcrumbs(Timesheet $timesheet, $suffix = null): array
    {
        return array_merge(
            (new ShowEmployee())->getBreadcrumbs([
                'organisation' => $this->organisation->slug,
                'employee'     => $timesheet->subject->slug
            ]),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.hr.employees.show',
                                'parameters' => [
                                    'organisation' => $this->organisation->slug,
                                    'employee'     => $timesheet->subject->slug,
                                    'tab'          => EmployeeTabsEnum::TIMESHEETS->value
                                ]
                            ],
                            'label' => __('timesheet')
                        ],
                        'model' => [
                            'label' => $timesheet->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(Timesheet $timesheet, ActionRequest $request): ?array
    {
        $previous = Timesheet::where('slug', '<', $timesheet->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Timesheet $timesheet, ActionRequest $request): ?array
    {
        $next = Timesheet::where('slug', '>', $timesheet->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Timesheet $timesheet, string $routeName): ?array
    {
        if(!$timesheet) {
            return null;
        }

        return match ($routeName) {
            'grp.org.hr.employees.show.timesheets.show' => [
                'label'=> $timesheet->subject->contact_name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation' => $this->organisation->slug,
                        'employee'     => $timesheet->subject->slug,
                        'timesheet'    => $timesheet->slug,
                    ]
                ]
            ]
        };
    }
}
