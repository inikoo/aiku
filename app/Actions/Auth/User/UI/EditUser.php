<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Auth\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditUser extends InertiaAction
{
    public function handle(User $user): User
    {
        return $user;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('sysadmin.edit');
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function asController(User $user, ActionRequest $request): User
    {
        $this->initialisation($request);

        return $this->handle($user);
    }



    public function htmlResponse(User $user, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('user'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'title'     => $user->username,
                    'actions'=>[
                      [
                          'type'=>'button',
                          'style'=>'exitEdit',
                          'route' => [
                              'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                              'parameters' => array_values($this->originalParameters)
                          ]
                      ]
                    ],
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'    => __('id'),
                             'icon'    => 'fa-light fa-user',
                             'current' => true,
                            'fields'   => [
                                'username' => [
                                    'type'  => 'input',
                                    'label' => __('username'),
                                    'value' => $user->username
                                ],
                                'email' => [
                                    'type'  => 'input',
                                    'label' => __('email'),
                                    'value' => $user->email
                                ],

                            ]
                        ],
                        'password'   => [
                            'title'   => __('Password'),
                            'icon'    => 'fa-light fa-key',
                            'current' => false,
                            'fields'  => [
                                'password' => [
                                    'type'  => 'password',
                                    'label' => __('password'),
                                    'value' => ''
                                ],
                            ]
                        ],
                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.user.update',
                            'parameters'=> [$user->username]

                        ],
                    ]
                ]
            ]
        );
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowUser::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('editing').')'
        );
    }
}
