<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:14:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\InertiaAction;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditEmployee extends InertiaAction
{
    public function handle(Employee $employee): Employee
    {
        return $employee;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }

    public function asController(Employee $employee, ActionRequest $request): Employee
    {
        $this->initialisation($request);

        return $this->handle($employee);
    }


    /**
     * @throws \Exception
     */
    public function htmlResponse(Employee $employee): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('employee'),
                'breadcrumbs' => $this->getBreadcrumbs($employee),
                'pageHead'    => [
                    'title'    => $employee->contact_name,
                    'exitEdit' => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters),
                        ]
                    ],
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('personal information'),
                            'fields' => [

                                'contact_name' => [
                                    'type'        => 'input',
                                    'label'       => __('name'),
                                    'placeholder' => 'Name',
                                    'value'       => $employee->contact_name
                                ],
                                'date_of_birth' => [
                                    'type'        => 'date',
                                    'label'       => __('date of birth'),
                                    'placeholder' => 'Date Of Birth',
                                    'value'       => $employee->date_of_birth
                                ],
                                'job_title' => [
                                    'type'        => 'select',
                                    'label'       => __(' position'),
                                    'options'     => Options::forModels(JobPosition::class, label: 'name', value: 'name'),
                                    'placeholder' => 'Select a Position',
                                    'mode'        => 'single',
                                    'value'       => $employee->job_title
                                ],
                                'state' => [
                                    'type'        => 'select',
                                    'label'       => __(' state'),
                                    'options'     => Options::forEnum(EmployeeStateEnum::class),
                                    'placeholder' => 'Select a State',
                                    'mode'        => 'single',
                                    'value'       => $employee->state
                                ]
                            ]
                        ]

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'models.employee.update',
                            'parameters' => $employee->slug

                        ],
                    ]

                ],

            ]
        );
    }

    public function getBreadcrumbs(Employee $employee): array
    {
        return ShowEmployee::make()->getBreadcrumbs(employee:$employee, suffix: '('.__('editing').')');
    }
}
