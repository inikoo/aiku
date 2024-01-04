<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:14:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\InertiaOrganisationAction;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\Market\ShopResource;
use App\Http\Resources\SysAdmin\Organisation\OrganisationResource;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\Inventory\Warehouse;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditEmployee extends InertiaOrganisationAction
{
    public function handle(Employee $employee): Employee
    {
        return $employee;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.edit");
    }

    public function asController(Organisation $organisation, Employee $employee, ActionRequest $request): Employee
    {
        $this->initialisation($organisation, $request);

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
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'pageHead'    => [
                    'title'    => $employee->contact_name,
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
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


                            ]
                        ],
                        [
                            'title'  => __('Employment'),
                            'fields' => [
                                'worker_number'       => [
                                    'type'     => 'input',
                                    'label'    => __('worker number'),
                                    'required' => true,
                                    'value'    => $employee->worker_number
                                ],
                                'alias'               => [
                                    'type'     => 'input',
                                    'label'    => __('alias'),
                                    'required' => true,
                                    'value'    => $employee->alias
                                ],
                                'work_email'          => [
                                    'type'  => 'input',
                                    'label' => __('work email'),
                                    'value' => $employee->work_email
                                ],
                                'state'               => [
                                    'label'    => __('state'),
                                    'type'     => 'radio',
                                    'mode'     => 'card',
                                    'required' => true,
                                    'value'    => $employee->state,
                                    'options'  => [
                                        [
                                            'title'       => __('Hired'),
                                            'description' => __('Will start in future date'),
                                            'value'       => EmployeeStateEnum::HIRED->value
                                        ],
                                        [
                                            'title'       => __('Working'),
                                            'description' => __('Employee already working'),
                                            'value'       => EmployeeStateEnum::WORKING->value
                                        ],
                                    ]
                                ],
                                'employment_start_at' => [
                                    'type'     => 'date',
                                    'label'    => __('employment start at'),
                                    'value'    => $employee->employment_start_at,
                                    'required' => true
                                ],
                            ]
                        ],
                        [
                            'title'  => __('job'),
                            'fields' => [
                                'job_title' => [
                                    'type'        => 'input',
                                    'label'       => __('job title'),
                                    'placeholder' => __('Job title'),
                                    'searchable'  => true,
                                    'value'       => $employee->job_title
                                ],
                                'positions' => [
                                    'type'     => 'employeePosition',
                                    'required' => true,
                                    'label'    => __('position'),
                                    'options'  => [
                                        'positions'     => JobPositionResource::collection(JobPosition::all()),
                                        'organisations' => OrganisationResource::collection(Organisation::all()),
                                        'shops'         => ShopResource::collection(Shop::all()),
                                        'warehouses'    => WarehouseResource::collection(Warehouse::all()),
                                    ],
                                    'value'    => [],
                                    'full'     => true
                                ],
                            ]
                        ],
                        [
                            'title'  => __('User credentials'),
                            'fields' => [

                                'username' => [
                                    'type'  => 'input',
                                    'label' => __('username'),
                                    'value' => ''

                                ],
                                'password' => [
                                    'type'  => 'password',
                                    'label' => __('password'),
                                    'value' => ''
                                ],
                            ]
                        ],

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.employee.update',
                            'parameters' => $employee->slug

                        ],
                    ]

                ],

            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return ShowEmployee::make()->getBreadcrumbs(routeParameters:$routeParameters, suffix: '('.__('editing').')');
    }
}
