<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

use App\Actions\Auth\UserRequest\IndexUserRequestLogs;
use App\Actions\InertiaAction;
use App\Actions\UI\SysAdmin\SysAdminDashboard;
use App\Enums\UI\UsersTabsEnum;
use App\Http\Resources\SysAdmin\UserRequestLogsResource;
use App\Http\Resources\SysAdmin\UserResource;
use App\InertiaTable\InertiaTable;
use App\Models\Auth\User;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexUsers extends InertiaAction
{
    public function __construct()
    {
        $this->elementGroups =
            [
                'status' => [
                    'label'    => __('Status'),
                    'elements' => ['active' => __('Active'), 'suspended' => __('Suspended')],
                    'engine'   => function ($query, $elements) {
                        $query->where('status', array_pop($elements) === 'active');
                    }

                ],
                'type'   => [
                    'label'    => __('Type'),
                    'elements' => ['employee' => __('Employee'), 'guest' => __('Guest')],
                    'engine'   => function ($query, $elements) {
                        $query->whereIn(
                            'parent_type',
                            Arr::map($elements, function (string $value, string $key) {
                                return ucfirst($value);
                            })
                        );
                    }

                ],
            ];
    }


    public function handle($prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('contact_name', $value)
                    ->orWhere('users.username', 'ILIKE', "$value%");
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $queryBuilder = QueryBuilder::for(User::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }


        return $queryBuilder->with('parent')
            ->defaultSort('username')
            ->select(['username', 'parent_type', 'parent_id', 'email', 'contact_name'])
            ->allowedSorts(['username', 'email', 'parent_type', 'contact_name'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: $prefix ? $prefix.'Page' : 'page'
            )
            ->withQueryString();
    }

    public function tableStructure(?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            foreach ($this->elementGroups as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }


            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'username', label: __('username'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'contact_name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'parent_type', label: __('type'), canBeHidden: false, sortable: true)
                ->defaultSort('username');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('sysadmin.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('sysadmin.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $users): AnonymousResourceCollection
    {
        return UserResource::collection($users);
    }


    public function htmlResponse(LengthAwarePaginator $users, ActionRequest $request): Response
    {
        return Inertia::render(
            'SysAdmin/Users',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('users'),
                'pageHead'    => [
                    'title' => __('users'),

                    // Remember to not create new Users on IndexUsers, only in employees and guest

                ],
                'labels'      => [
                    'usernameNoSet' => __('username no set')
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => UsersTabsEnum::navigation(),
                ],

                UsersTabsEnum::USERS->value => $this->tab == UsersTabsEnum::USERS->value ?
                    fn () => UserResource::collection($users)
                    : Inertia::lazy(fn () => UserResource::collection($users)),

                UsersTabsEnum::USERS_REQUESTS->value => $this->tab == UsersTabsEnum::USERS_REQUESTS->value ?
                    fn () => UserRequestLogsResource::collection(IndexUserRequestLogs::run())
                    : Inertia::lazy(fn () => UserRequestLogsResource::collection(IndexUserRequestLogs::run()))

            ]
        )->table(
            $this->tableStructure(
                prefix: 'users'
            )
        )
            ->table(IndexUserRequestLogs::make()->tableStructure());
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request)->withTab(UsersTabsEnum::values());

        return $this->handle(prefix: 'users');
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('users'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'sysadmin.users.index' =>
            array_merge(
                SysAdminDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'sysadmin.users.index',
                        null
                    ]
                ),
            ),


            default => []
        };
    }

}
