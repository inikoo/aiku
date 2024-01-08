<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\InertiaAction;
use App\Models\Market\ProductCategory;
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
                    'title'        => __('new job position'),

                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.org.hr.job-positions.index',
                                'parameters' => array_values($this->originalParameters)
                            ],
                        ]
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('creating job positions'),
                            'fields' => [
                                'code' => [
                                    'type'      => 'input',
                                    'label'     => __('code'),
                                    'required'  => true
                                ],
                                'name' => [
                                    'type'      => 'input',
                                    'label'     => __('name'),
                                    'required'  => true
                                ],
                                'department' => [
                                    'type'        => 'select',
                                    'label'       => __('department'),
                                    'options'     => Options::forModels(ProductCategory::class, label: 'name', value: 'name'),
                                    'placeholder' => __('Select a department'),
                                    'mode'        => 'single',
                                ]
                            ]
                        ]

                    ],
                    'route'      => [
                            'name'       => 'grp.models.job-position.store',

                    ]

                ],



            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('hr.edit');
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
