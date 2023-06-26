<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 13:03:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest\UI;

use App\Actions\InertiaAction;
use App\Enums\Auth\Guest\GuestTypeEnum;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateGuest extends InertiaAction
{
    /**
     * @throws \Exception
     */
    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                ),
                'title'       => __('new guest'),
                'pageHead'    => [
                    'title'        => __('new guest'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'sysadmin.guests.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('Type/Login credentials'),

                            'fields' => [
                                'type' => [
                                    'type'         => 'radio',
                                    'label'        => __('type'),
                                    'value'        => GuestTypeEnum::CONTRACTOR->value,
                                    'options'      => Options::forEnum(GuestTypeEnum::class)
                                ],
                                'group_type' => [
                                    'type'         => 'fork_type_group_user',
                                    'label'        => '',
                                    'value'        => GuestTypeEnum::CONTRACTOR->value,
                                    'options'      => [
                                        [
                                            'label'      => __('Create new user'),
                                            'value'      => 'newGroupUser',
                                            'route'      => [
                                                'name'       => 'models.group-user.guest.store',
                                            ]
                                        ],
                                        [
                                        'label'=> __('Use existing user from other aiku account'),
                                        'value'=> 'existingGroupUser'
                                        ]
                                    ]
                                ],
                                /*
                                'username' => [
                                    'type'   => 'input',
                                    'label'  => __('username'),
                                    'value'  => '',
                                    'required'=>true,
                                ],
                                */

                            ]
                        ],
                        [
                            'title'  => __('personal information'),
                            'fields' => [
                                'company_name' => [
                                    'type'   => 'input',
                                    'label'  => __('company'),
                                    'value'  => '',
                                ],
                                'contact_name' => [
                                    'type'    => 'input',
                                    'label'   => __('name'),
                                    'value'   => '',
                                    'required'=> true
                                ],
                                'phone' => [
                                    'type'  => 'phone',
                                    'label' => __('phone'),
                                    'value' => ''
                                ],
                                'email' => [
                                    'type'  => 'input',
                                    'label' => __('email'),
                                    'value' => ''
                                ],
                            ]
                        ],

                    ],
                    'route'      => [
                        'name'       => 'models.guest.store',

                    ]

                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('sysadmin.users.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }

    public function getBreadcrumbs($suffix = null): array
    {
        return array_merge(
            IndexGuest::make()->getBreadcrumbs(
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating user'),
                    ]
                ]
            ]
        );
    }
}
