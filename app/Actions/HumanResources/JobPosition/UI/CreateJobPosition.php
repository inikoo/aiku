<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\InertiaAction;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\HumanResources\JobPosition;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateJobPosition extends InertiaAction
{
    /**
     * @throws Exception
     */
    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new job position'),
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
                            'title'  => __('creating job positions'),
                            'fields' => [
                                'name' => [
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
                                    'label'       => __('position'),
                                    'options'     => Options::forModels(JobPosition::class, label: 'name', value: 'name'),
                                    'placeholder' => 'Select a Position',
                                    'mode'        => 'single'
                                ],
                                'state' => [
                                    'type'        => 'select',
                                    'label'       => __('state'),
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


    /**
     * @throws Exception
     */
    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            IndexJobPositions::make()->getBreadcrumbs(),
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
