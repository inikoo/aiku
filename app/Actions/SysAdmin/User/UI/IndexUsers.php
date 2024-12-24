<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\Analytics\UserRequest\UI\IndexUserRequestLogs;
use App\Actions\GrpAction;
use App\Actions\Helpers\History\UI\IndexHistory;
use App\Actions\SysAdmin\User\WithUsersSubNavigation;
use App\Actions\SysAdmin\WithSysAdminAuthorization;
use App\Actions\UI\Grp\SysAdmin\ShowSysAdminDashboard;
use App\Enums\UI\SysAdmin\UsersTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\SysAdmin\UserRequestLogsResource;
use App\Http\Resources\SysAdmin\UsersResource;
use App\InertiaTable\InertiaTable;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexUsers extends GrpAction
{
    use WithSysAdminAuthorization;
    use WithUsersSubNavigation;

    private string $scope; 

    protected function getElementGroups(Group $group): array
    {
        return
            [
                'status' => [
                    'label'    => __('Status'),
                    'elements' => [
                        'active'    =>
                            [
                                __('Active'),
                                $group->sysadminStats->number_users_status_active
                            ],
                        'suspended' => [
                            __('Suspended'),
                            $group->sysadminStats->number_users_status_inactive
                        ]
                    ],
                    'engine'   => function ($query, $elements) {
                        $query->where('status', array_pop($elements) === 'active');
                    }

                ],
            ];
    }

    public function inSuspended(ActionRequest $request)
    {
        $group = group();
        $this->scope = 'suspended';
        $this->initialisation($group, $request);

        return $this->handle($group, 'suspended');
    }

    public function inAll(ActionRequest $request)
    {
        $group = group();
        $this->scope = 'all';
        $this->initialisation($group, $request);

        return $this->handle($group, 'all');
    }

    public function handle(Group $group, $scope = 'active', $prefix = null): LengthAwarePaginator
    {
        $this->group  = $group;
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('contact_name', $value)
                    ->orWhereStartWith('users.username', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $queryBuilder = QueryBuilder::for(User::class);

        if ($scope == 'active') {
            $queryBuilder->where('status', true);
        } elseif ($scope == 'suspended') {
            $queryBuilder->where('status', false);
        } elseif ($scope == 'all') {
            foreach ($this->getElementGroups($group) as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }

        return $queryBuilder
            ->defaultSort('username')
            ->select(['username', 'email', 'contact_name', 'image_id', 'status'])
            ->allowedSorts(['username', 'email', 'contact_name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Group $group, string $scope = 'active', ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $group, $scope) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            if ($scope == 'all') {
                foreach ($this->getElementGroups($group) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }

            $table
                ->withTitle(title: __('Users'))
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'status', label: ['data' => ['fal', 'fa-yin-yang'], 'type' => 'icon', 'tooltip' => __('status')], type: 'icon')
                ->column(key: 'image', label: ['data' => ['fal', 'fa-user-circle'], 'type' => 'icon', 'tooltip' => __('avatar')], type: 'avatar')
                ->column(key: 'username', label: __('username'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'contact_name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'parent_type', label: __('type'), canBeHidden: false, sortable: true)
                ->defaultSort('username');
        };
    }

    public function jsonResponse(LengthAwarePaginator $users): AnonymousResourceCollection
    {
        return UsersResource::collection($users);
    }

    public function htmlResponse(LengthAwarePaginator $users, ActionRequest $request): Response
    {
        $subNavigation = $this->getUsersNavigation($this->group, $request);
        $title = __('Active');
        $model = __('Users');
        $icon  = [
            'icon'  => ['fal', 'fa-user'],
            'title' => __('active users')
        ];
        if($this->scope == 'suspended')
        {
            $title = __('Suspended');
            $icon  = [
                'icon'  => ['fal', 'fa-user-slash'],
                'title' => __('suspended users')
            ];
        } elseif ($this->scope == 'all')
        {
            $title = __('All');
            $icon  = [
                'icon'  => ['fal', 'fa-users'],
                'title' => __('all users')
            ];
        }
        return Inertia::render(
            'SysAdmin/Users',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName()),
                'title'       => __('users'),
                'pageHead' => [
                    'title'         => $title,
                    'model'         => $model,
                    'icon'          => $icon,
                    'subNavigation' => $subNavigation,
                ],

                'labels' => [
                    'usernameNoSet' => __('username no set')
                ],

                'data'        => UsersResource::collection($users),
            ]
        )->table(
            $this->tableStructure(
                group: $this->group,
                scope: $this->scope,
            )
        );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->scope = 'active';
        $this->initialisation(app('group'), $request)->withTab(UsersTabsEnum::values());

        return $this->handle(group: $this->group, prefix: 'users');
    }

    public function getBreadcrumbs(string $routeName): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Users'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.sysadmin.users.index' =>
            array_merge(
                ShowSysAdminDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.sysadmin.users.index',
                        null
                    ]
                ),
            ),
            'grp.sysadmin.users.suspended.index' =>
            array_merge(
                ShowSysAdminDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.sysadmin.users.suspended.index',
                        null
                    ]
                ),
            ),
            'grp.sysadmin.users.all.index' =>
            array_merge(
                ShowSysAdminDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.sysadmin.users.all.index',
                        null
                    ]
                ),
            ),


            default => []
        };
    }

}
