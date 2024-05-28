<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:09:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\OrgAction;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Catalogue\ShopResource;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateEmployee extends OrgAction
{
    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('new employee'),
                'pageHead'    => [
                    'title'   => __('new employee'),
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.org.hr.employees.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('personal information'),
                            'fields' => [
                                'contact_name'  => [
                                    'type'     => 'input',
                                    'label'    => __('name'),
                                    'required' => true,
                                ],
                                'date_of_birth' => [
                                    'type'  => 'date',
                                    'label' => __('date of birth'),
                                    'value' => ''
                                ],
                                'email'         => [
                                    'type'  => 'input',
                                    'label' => __('personal email'),
                                    'value' => ''
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
                                    'value'    => ''
                                ],
                                'alias'               => [
                                    'type'     => 'input',
                                    'label'    => __('alias'),
                                    'required' => true,
                                    'value'    => ''
                                ],
                                'work_email'          => [
                                    'type'  => 'input',
                                    'label' => __('work email'),
                                    'value' => ''
                                ],
                                'state'               => [
                                    'label'    => __('state'),
                                    'type'     => 'radio',
                                    'mode'     => 'card',
                                    'required' => true,
                                    'value'    => EmployeeStateEnum::HIRED->value,
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
                                    'value'    => '',
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
                                    'value'       => ''
                                ],
                                'positions' => [
                                    'type'     => 'employeePosition',
                                    'required' => true,
                                    'label'    => __('position'),
                                    'options'  => [
                                        'positions'           => JobPositionResource::collection($this->organisation->jobPositions),
                                        'shops'               => ShopResource::collection($this->organisation->shops()->where('type', '!=', ShopTypeEnum::FULFILMENT)->get()),
                                        'fulfilments'         => ShopResource::collection($this->organisation->shops()->where('type', '=', ShopTypeEnum::FULFILMENT)->get()),
                                        'warehouses'          => WarehouseResource::collection($this->organisation->warehouses),
                                    ],
                                    'value'    => new \stdClass(),
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
                    'route'     => [
                        'name'       => 'grp.models.org.employee.store',
                        'parameters' => [
                            'organisation' => $this->organisation->id
                        ]
                    ]
                ],
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }


    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->initialisation($organisation, $request);

        return $request;
    }


    public function getBreadcrumbs($routeParameters): array
    {
        return array_merge(
            IndexEmployees::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Creating employee'),
                    ]
                ]
            ]
        );
    }

}
