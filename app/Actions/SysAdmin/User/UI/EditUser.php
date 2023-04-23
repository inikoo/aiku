<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Auth\User;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class EditUser extends InertiaAction
{
    use HasUIUser;
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



    public function htmlResponse(User $user): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('user'),
                'breadcrumbs' => $this->getBreadcrumbs($user),
                'pageHead'    => [
                    'title'     => $user->username,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
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
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.user.update',
                            'parameters'=> $user->username

                        ],
                    ]
                ]
            ]
        );
    }

    #[Pure] public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }
}
