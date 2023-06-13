<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:14:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\WorkingPlace\UI;

use App\Actions\InertiaAction;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use App\Models\HumanResources\Workplace;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditWorkingPlace extends InertiaAction
{
    public function handle(Workplace $workplace): Workplace
    {
        return $workplace;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }

    public function asController(Workplace $workplace, ActionRequest $request): Workplace
    {
        $this->initialisation($request);

        return $this->handle($workplace);
    }


    /**
     * @throws \Exception
     */
    public function htmlResponse(Workplace $workplace): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('employee'),
                'breadcrumbs' => $this->getBreadcrumbs($workplace),
                'pageHead'    => [
                    'title'    => $workplace->name,
                    'exitEdit' => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters),
                        ]
                    ],
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('name'),
                            'fields' => [
                                'name' => [
                                    'type'        => 'input',
                                    'label'       => __('name'),
                                    'placeholder' => 'Name',
                                    'value'       => $workplace->name
                                ],
                                'type' => [
                                    'type'        => 'select',
                                    'label'       => __('type'),
                                    'options'     => Options::forEnum(WorkplaceTypeEnum::class),
                                    'placeholder' => 'Select a Type',
                                    'mode'        => 'single',
                                    'value'       => $workplace->type
                                ]
                            ]
                        ],
                        [
                            'title'  => __('Owner'),
                            'fields' => [
                                'owner_id' => [
                                    'type'        => 'input',
                                    'label'       => __('owner'),
                                    'placeholder' => 'Owner IDs',
                                    'value'       => $workplace->owner_id
                                ],
                                'owner_type' => [
                                    'type'        => 'input',
                                    'label'       => __('type'),
                                    'placeholder' => 'Owner Type',
                                    'value'       => $workplace->owner_type
                                ]
                            ]
                        ]

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'models.employee.update',
                            'parameters' => $workplace->slug

                        ],
                    ]

                ],

            ]
        );
    }

    public function getBreadcrumbs(Workplace $workplace): array
    {
        return ShowWorkingPlace::make()->getBreadcrumbs(workplace: $workplace, suffix: '('.__('editing').')');
    }
}
