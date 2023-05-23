<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\SysAdmin\SysAdminDashboard;
use App\Enums\UI\UserTabsEnum;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Auth\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowUser extends InertiaAction
{
    public function asController(User $user, ActionRequest $request): User
    {
        $this->initialisation($request)->withTab(UserTabsEnum::values());
        return $user;
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('sysadmin.users.edit');
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function htmlResponse(User $user, ActionRequest $request): Response
    {
        $this->validateAttributes();

        return Inertia::render(
            'SysAdmin/User',
            [
                'title'       => __('user'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($user, $request),
                    'next'     => $this->getNext($user, $request),
                ],
                'pageHead'    => [
                    'title'     => $user->username,
                    'edit'      => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'capitalize'=> false

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => UserTabsEnum::navigation()
                ]
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {

        $headCrumb = function (User $user, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('users')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $user->username,
                        ],

                    ],
                    'suffix'=> $suffix

                ],
            ];
        };

        return match ($routeName) {
            'sysadmin.users.show',
            'sysadmin.users.edit' =>

            array_merge(
                SysAdminDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['user'],
                    [
                        'index' => [
                            'name'       => 'sysadmin.users.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'sysadmin.users.show',
                            'parameters' => [$routeParameters['user']->username]
                        ]
                    ],
                    $suffix
                ),
            ),


            default => []
        };

    }

    public function getPrevious(User $user, ActionRequest $request): ?array
    {
        $previous = User::where('username', '<', $user->username)->orderBy('username', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(User $user, ActionRequest $request): ?array
    {
        $next = User::where('username', '>', $user->username)->orderBy('username')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?User $user, string $routeName): ?array
    {
        if(!$user) {
            return null;
        }
        return match ($routeName) {
            'sysadmin.users.show'=> [
                'label'=> $user->username,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'user'=> $user->username
                    ]

                ]
            ]
        };
    }
}
