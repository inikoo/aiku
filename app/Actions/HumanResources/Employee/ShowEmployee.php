<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 08 Sept 2022 00:30:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\HumanResources\ShowHumanResourcesDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


class ShowEmployee
{
    use AsAction;
    use WithInertia;

    public function handle(Employee $employee): Employee
    {
        return $employee;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.view");
    }

    public function asController(Employee $employee): Employee
    {
        return $this->handle($employee);
    }

    public function htmlResponse(Employee $employee): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'HumanResources/Employee',
            [
                'title'=>__('employee'),
                'breadcrumbs' => $this->getBreadcrumbs($employee),
                'pageHead'=>[
                    'title'=>$employee->name,
                    'meta'=>[
                        [
                            'name'=>$employee->worker_number,
                            'leftIcon'=>[
                                'icon'=>'fal fa-id-card',
                                'tooltip'=>__('Worker number')
                            ]
                        ]
                    ]
                ],
                'employee'    => $employee
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Employee $employee): EmployeeResource
    {
        return new EmployeeResource($employee);
    }


    public function getBreadcrumbs(Employee $employee): array
    {
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs(),
            [
                'hr.employees.show' => [
                    'route'           => 'hr.employees.show',
                    'routeParameters' => $employee->id,
                    'name'            => $employee->code,
                    'index'           => [
                        'route'   => 'hr.employees.index',
                        'overlay' => __('Employees list')
                    ],
                    'modelLabel'      => [
                        'label' => __('employee')
                    ],
                ],
            ]
        );
    }

}
