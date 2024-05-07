<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\OrgAction;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateJobPosition extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => __('new job position'),
                'pageHead'    => [
                    'title'   => __('new job position'),
                    'actions' => [
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
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('creating job positions'),
                            'fields' => [
                                'code' => [
                                    'type'     => 'inputWithAddOn',
                                    'leftAddOn'=> [
                                        'label' => 'C-'
                                    ],
                                    'label'       => __('code'),
                                    'required'    => true,
                                    'placeholder' => __('Enter job code'),
                                    'value'       => ''
                                ],
                                'name' => [
                                    'type'        => 'input',
                                    'label'       => __('name'),
                                    'required'    => true,
                                    'placeholder' => __('Enter job name'),
                                    'value'       => ''
                                ],

                            ]
                        ]

                    ],
                    'route'     => [
                        'name'       => 'grp.models.org.job-position.store',
                        'parameters' => [$this->organisation->id]

                    ]

                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("org-supervisor.{$this->organisation->id}.human-resources");
    }


    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

        return $this->handle($request);
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexJobPositions::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating job position')
                    ]
                ]
            ]
        );
    }

}
