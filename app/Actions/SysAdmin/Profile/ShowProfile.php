<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 29 Mar 2022 00:42:13 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\SysAdmin\Profile;

use App\Actions\UI\WithInertia;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Sysadmin\User;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

use function __;
use function app;


/**
 * @property User $user
 * @property array $breadcrumbs
 * @property bool $canEdit
 * @property bool $canViewEmployees
 */
class ShowProfile
{
    use AsAction;
    use WithInertia;


    public function handle(User $user): User
    {
        return $user;
    }

    #[Pure] public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function authorize(ActionRequest $request): bool
    {
        return  $request->user()->hasPermissionTo("account.users.view");
    }

    public function asInertia(User $user, array $attributes = []): Response
    {
        $this->set('user', $user)->fill($attributes);
        $this->validateAttributes();


        $actionIcons = [];


        /*
        $actionIcons['account.users.logbook'] =[
            'routeParameters' => $this->user->id,
            'name'            => __('History'),
            'icon'            => ['fal', 'history']
        ];
        */

        if ($this->canEdit) {
            $actionIcons[] = [
                'route'           => 'account.users.edit',
                'routeParameters' => $this->user->id,
                'name'            => __('Edit'),
                'icon'            => ['fal', 'edit']
            ];
        }

        /** @var \App\Models\HumanResources\Employee|\App\Models\HumanResources\Guest $userable */
        $userable = $this->user->userable;

        return Inertia::render(
            'show-model',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->user),
                'navData'     => ['module' => 'account', 'sectionRoot' => 'account.users.index'],

                'headerData' => [
                    'title' => $this->user->username,

                    'info' => [
                        [
                            'type' => 'group',
                            'data' => [
                                'components' => [
                                    [
                                        'type' => 'icon',
                                        'data' => array_merge(
                                            [
                                                'type' => 'page-header',
                                                'icon' => $this->user->type_icon
                                            ],

                                        )
                                    ],
                                    [
                                        'type' => ($this->user->userable_type == 'Guest' || $this->canViewEmployees) ? 'link' : 'text',
                                        'data' => [
                                            'slot' => $userable->name,
                                            'href' => match ($this->user->userable_type) {
                                                'Employee' => [
                                                    'route'      => 'human_resources.employees.show',
                                                    'parameters' => $this->user->userable_id
                                                ],
                                                'Guest' => [
                                                    'route'      => 'account.guests.show',
                                                    'parameters' => $this->user->userable_id
                                                ],
                                                default => null
                                            }
                                        ]
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => [
                                            'slot' => " ({$this->user->localised_userable_type})"
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'group',
                            'data' => [
                                'components' => [
                                    [
                                        'type' => 'icon',
                                        'data' => array_merge(
                                            ['type' => 'page-header',],
                                            match ($this->user->status) {
                                                true => [
                                                    'icon'  => 'check-circle',
                                                    'class' => 'text-green-600',
                                                ],
                                                default => [
                                                    'icon'  => 'times-circle',
                                                    'class' => 'text-red-700',
                                                ]
                                            }
                                        )
                                    ],
                                    [
                                        'type' => 'text',
                                        'data' => [
                                            'slot' => $this->user->status ? __('Active') : __('Blocked')
                                        ]
                                    ]
                                ]
                            ]
                        ]


                    ],

                    'actionIcons' => $actionIcons,


                ],
                'model'      => $this->user
            ]

        );
    }


    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set(
            'canEdit',
            ($request->user()->can('users.edit') and $this->user->userable_type != 'Tenant')
        );

        $this->set('canViewEmployees', $request->user()->can('hr.view'));
    }

    public function getBreadcrumbs(User $user): array
    {
        return array_merge(
            (new ShowTenant())->getBreadcrumbs(),
            [
                'account.users.show' => [
                    'route'           => 'account.users.show',
                    'routeParameters' => $user->id,
                    'index'           => [
                        'route'   => 'account.users.index',
                        'overlay' => __("users' list")
                    ],
                    'modelLabel'      => [
                        'label' => __('user')
                    ],
                    'name'            => $user->username,

                ],
            ]
        );
    }


}
