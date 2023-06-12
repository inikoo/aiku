<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:09:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\WorkingPlace\UI;

use App\Actions\InertiaAction;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateWorkingPlace extends InertiaAction
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
                'title'       => __('new workplace'),
                'pageHead'    => [
                    'title'        => __('new workplace'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'hr.working-places.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('name'),
                            'fields' => [
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                ],
                                'type' => [
                                    'type'        => 'select',
                                    'label'       => __(' type'),
                                    'options'     => Options::forEnum(WorkplaceTypeEnum::class),
                                    'placeholder' => 'Select a Type',
                                    'mode'        => 'single',
                                ]
                            ]
                        ],
                        [
                            'title'  => __('Owner'),
                            'fields' => [
                                'owner_id' => [
                                    'type'        => 'input',
                                    'label'       => __('owner'),
                                    'placeholder' => 'Owner'
                                ],
                                'owner_type' => [
                                    'type'        => 'input',
                                    'label'       => __('type'),
                                    'placeholder' => 'Type'
                                ]
                            ]
                        ]

                    ],
                    'route'      => [
                            'name'       => 'models.working-place.store',

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
            IndexWorkingPlaces::make()->getBreadcrumbs(),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating workplace'),
                    ]
                ]
            ]
        );
    }

}
