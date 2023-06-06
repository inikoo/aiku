<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:09:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\InertiaAction;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\HumanResources\JobPosition;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateEmployee extends InertiaAction
{
    /**
     * @throws \Exception
     */
    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new employee'),
                'pageHead'    => [
                    'title'        => __('new employee'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'hr.employees.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('personal information'),
                            'fields' => [
                                'contact_name' => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                ],
                                'date_of_birth' => [
                                    'type'  => 'date',
                                    'label' => __('date of birth'),
                                    'value' => ''
                                ],
                                'job_title' => [
                                    'type'        => 'select',
                                    'label'       => __(' position'),
                                    'options'     => Options::forModels(JobPosition::class, label: 'name', value: 'name'),
                                    'placeholder' => 'Select a Position',
                                    'mode'        => 'single'
                                ],
                                'state' => [
                                    'type'        => 'select',
                                    'label'       => __(' state'),
                                    'options'     => Options::forEnum(EmployeeStateEnum::class),
                                    'placeholder' => 'Select a State',
                                    'mode'        => 'single'
                                ]

                            ]
                        ]

                    ],
                    'route'      => [
                            'name'       => 'models.employee.store',

                    ]

                ],



            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('hr.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            IndexEmployees::make()->getBreadcrumbs(),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating employee'),
                    ]
                ]
            ]
        );
    }

}
