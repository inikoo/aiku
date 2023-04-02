<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:13:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\EmployeeTabsEnum;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowEmployee extends InertiaAction
{
    use HasUIEmployee;

    public function handle(Employee $employee): Employee
    {
        return $employee;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.edit');
        return $request->user()->hasPermissionTo("hr.view");
    }

    public function asController(Employee $employee, ActionRequest $request): Employee
    {
        $this->initialisation($request)->withTab(EmployeeTabsEnum::values());
        return $this->handle($employee);
    }

    public function htmlResponse(Employee $employee): Response
    {
        return Inertia::render(
            'HumanResources/Employee',
            [
                'title'       => __('employee'),
                'breadcrumbs' => $this->getBreadcrumbs($employee),
                'pageHead'    => [
                    'title' => $employee->name,
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
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => EmployeeTabsEnum::navigation()
                ]            ]
        );
    }



   public function jsonResponse(Employee $employee): EmployeeResource
   {
       return new EmployeeResource($employee);
   }
}
