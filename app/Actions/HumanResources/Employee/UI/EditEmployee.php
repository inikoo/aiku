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
use Exception;
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
     * @throws Exception
     */
    public function htmlResponse(Employee $employee, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('employee'),
                'breadcrumbs' => $this->getBreadcrumbs($employee),
                'pageHead'    => [
                    'title'    => $employee->contact_name,
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('personal information'),
                            'fields' => [

                                'contact_name' => [
                                    'type'        => 'input',
                                    'label'       => __('name'),
                                    'placeholder' => __('Name'),
                                    'value'       => $employee->contact_name
                                ],
                                'date_of_birth' => [
                                    'type'        => 'date',
                                    'label'       => __('date of birth'),
                                    'placeholder' => __('Date of birth'),
                                    'value'       => $employee->date_of_birth
                                ],
                                'job_title' => [
                                    'type'        => 'select',
                                    'label'       => __('position'),
                                    'options'     => Options::forModels(JobPosition::class, label: 'name', value: 'name'),
                                    'placeholder' => __('Select a job position'),
                                    'mode'        => 'single',
                                    'value'       => $employee->job_title,
                                    'searchable'  => true
                                ],
                                'state' => [
                                    'type'        => 'select',
                                    'label'       => __('state'),
                                    'options'     => Options::forEnum(EmployeeStateEnum::class),
                                    'placeholder' => __('Select a state'),
                                    'mode'        => 'single',
                                    'value'       => $employee->state,
                                    'searchable'  => true
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
