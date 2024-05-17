<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\GrpAction;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\SysAdmin\UserRequest\ShowUserRequestLogs;
use App\Actions\Traits\WithElasticsearch;
use App\Actions\UI\Grp\SysAdmin\ShowSysAdminDashboard;
use App\Enums\UI\SysAdmin\UserTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\SysAdmin\UserRequestLogsResource;
use App\Http\Resources\SysAdmin\UserResource;
use App\Http\Resources\SysAdmin\UserShowcaseResource;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowUser extends GrpAction
{
    use WithElasticsearch;

    public function asController(User $user, ActionRequest $request): User
    {
        $this->initialisation(app('group'), $request)->withTab(UserTabsEnum::values());
        return $user;
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('sysadmin.edit');
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function htmlResponse(User $user, ActionRequest $request): Response
    {
        return Inertia::render(
            'SysAdmin/User',
            [
                'title'       => __('user'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($user, $request),
                    'next'     => $this->getNext($user, $request),
                ],
                'pageHead' => [
                    'title'   => $user->username,
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ]
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => UserTabsEnum::navigation()
                ],

                UserTabsEnum::SHOWCASE->value => $this->tab == UserTabsEnum::SHOWCASE->value ?
                    fn () => UserShowcaseResource::make($user)
                    : Inertia::lazy(fn () => UserShowcaseResource::make($user)),

                UserTabsEnum::REQUEST_LOGS->value => $this->tab == UserTabsEnum::REQUEST_LOGS->value ?
                    fn () => UserRequestLogsResource::collection(ShowUserRequestLogs::run($user->username))
                    : Inertia::lazy(fn () => UserRequestLogsResource::collection(ShowUserRequestLogs::run($user->username))),

                UserTabsEnum::ROLES->value => $this->tab == UserTabsEnum::ROLES->value ?
                    fn () => $user->roles->pluck('name')
                    : Inertia::lazy(fn () => $user->roles->pluck('name')),

                UserTabsEnum::PERMISSIONS->value => $this->tab == UserTabsEnum::PERMISSIONS->value ?
                    fn () => $user->getAllPermissions()->pluck('name')
                    : Inertia::lazy(fn () => $user->getAllPermissions()->pluck('name')),

                UserTabsEnum::HISTORY->value => $this->tab == UserTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($user))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($user)))

            ]
        )->table(ShowUserRequestLogs::make()->tableStructure())
            ->table(IndexHistory::make()->tableStructure(prefix: UserTabsEnum::HISTORY->value));
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
                    'suffix' => $suffix

                ],
            ];
        };

        $user=User::where('username', $routeParameters['user'])->first();

        return match ($routeName) {
            'grp.sysadmin.users.show',
            'grp.sysadmin.users.edit' =>

            array_merge(
                ShowSysAdminDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $user,
                    [
                        'index' => [
                            'name'       => 'grp.sysadmin.users.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'grp.sysadmin.users.show',
                            'parameters' => $user->username
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
        if (!$user) {
            return null;
        }
        return match ($routeName) {
            'grp.sysadmin.users.show' => [
                'label' => $user->username,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'user' => $user->username
                    ]

                ]
            ]
        };
    }
}
