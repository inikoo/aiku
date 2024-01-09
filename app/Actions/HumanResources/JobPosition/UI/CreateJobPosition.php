<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\InertiaOrganisationAction;
use App\Models\Market\ProductCategory;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateJobPosition extends InertiaOrganisationAction
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
                                'parameters' => ['organisation' => $this->organisation->slug]
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
                        'parameters'     => ['organisation' => $this->organisation->slug]

                    ]

                ],



            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.edit");
    }


    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

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
