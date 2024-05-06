<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:14:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\OrgAction;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\Market\ShopResource;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditEmployee extends OrgAction
{
    public function handle(Employee $employee): Employee
    {
        return $employee;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
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

        $sections['properties'] = [
            'label'  => __('Properties'),
            'icon'   => 'fal fa-sliders-h',
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
                    'value' => $employee->work_email ?? ''
                ],
                'state'               => [
                    'type'    => 'radio',
                    'mode'    => 'card',
                    'label'   => '',
                    'value'   => $employee->state,
                    'options' => [
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
                'job_title'           => [
                    'type'        => 'input',
                    'label'       => __('job title'),
                    'placeholder' => __('Job title'),
                    'searchable'  => true,
                    'value'       => $employee->job_title,
                    'required'    => true
                ],
                'positions' => [
                    'type'     => 'employeePosition',
                    'required' => true,
                    'label'    => __('position'),
                    'options'  => [
                        'positions'           => JobPositionResource::collection(JobPosition::all()),
                        'shops'               => ShopResource::collection($this->organisation->shops()->where('type', '!=', ShopTypeEnum::FULFILMENT)->get()),
                        'fulfilments'         => ShopResource::collection($this->organisation->shops()->where('type', '=', ShopTypeEnum::FULFILMENT)->get()),
                        'warehouses'          => WarehouseResource::collection($this->organisation->warehouses),
                    ],
                    'value'    => $employee->jobPositions->pluck('code')->map(function ($code) {
                        return $code;
                    }),
                    'full'     => true
                ],


            ]
        ];


        $sections['personal'] = [
            'label'  => __('Personal information'),
            'icon'   => 'fal fa-id-card',
            'fields' => [
                'contact_name'  => [
                    'type'        => 'input',
                    'label'       => __('name'),
                    'placeholder' => __('Name'),
                    'value'       => $employee->contact_name,
                    'required'    => true
                ],
                'date_of_birth' => [
                    'type'        => 'date',
                    'label'       => __('date of birth'),
                    'placeholder' => __('Date of birth'),
                    'value'       => $employee->date_of_birth
                ],
                'email'         => [
                    'type'  => 'input',
                    'label' => __('personal email'),
                    'value' => $employee->email
                ],
            ]
        ];

        $sections['credentials'] = [
            'label'  => __('Credentials'),
            'icon'   => 'fal fa-key',
            'fields' => [
                'username' => [
                    'type'  => 'input',
                    'label' => __('username'),
                    'value' => $employee->user ? $employee->user->username : ''

                ],
                'password' => [
                    'type'  => 'password',
                    'label' => __('password'),

                ],
            ]
        ];

        $currentSection = 'properties';
        if ($request->has('section') and Arr::has($sections, $request->get('section'))) {
            $currentSection = $request->get('section');
        }

        return Inertia::render(
            'EditModel',
            [
                'live_users'=> [
                    'icon_left'   => [
                        'icon' => 'fal fa-user-hard-hat',
                        'class'=> 'text-lime-400'
                    ],
                    'icon_right'  => [
                        'icon' => 'fal fa-pencil',
                        'class'=> 'text-gray-300'
                    ],
                ],
                'title'       => __('employee'),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'pageHead'    => [
                    'title'    => $employee->contact_name,
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],

                'formData'    => [
                    'current'   => $currentSection,
                    'blueprint' => $sections,
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.employee.update',
                            'parameters' => [$employee->id]

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
