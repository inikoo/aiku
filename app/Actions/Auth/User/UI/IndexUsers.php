<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

use App\Actions\Elasticsearch\GetElasticsearchDocument;
use App\Actions\InertiaAction;
use App\Actions\Inventory\Warehouse\UI\GetWarehouseShowcase;
use App\Actions\UI\SysAdmin\SysAdminDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Enums\UI\UsersTabsEnum;
use App\Enums\UI\UserTabsEnum;
use App\Enums\UI\WarehouseTabsEnum;
use App\Http\Resources\SysAdmin\UserHistoryResource;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Auth\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexUsers extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('users.username', 'ILIKE', "%$value%");
            });
        });


        return QueryBuilder::for(User::class)
            ->with('parent')
            ->defaultSort('username')
            ->select(['username', 'parent_type', 'parent_id'])
            ->allowedSorts(['username', 'email', 'parent_type'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
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


    public function htmlResponse(LengthAwarePaginator $users, ActionRequest $request)
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
                    'title'  => __('users'),

                    // Remember to not create new Users on IndexUsers, only in employees and guest

                ],
                'labels'      => [
                    'usernameNoSet' => __('username no set')
                ],

                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => UsersTabsEnum::navigation(),
                ],

                UsersTabsEnum::USERS->value => $this->tab == UsersTabsEnum::USERS->value ?
                    fn () => UserResource::collection($users)
                    : Inertia::lazy(fn () => UserResource::collection($users)),

                UsersTabsEnum::USERS_REQUESTS->value => $this->tab == UsersTabsEnum::USERS_REQUESTS->value ?
                    fn () => UserHistoryResource::collection(GetElasticsearchDocument::run())
                    : Inertia::lazy(fn () => UserHistoryResource::collection(GetElasticsearchDocument::run()))

            ]
        )->table(function (InertiaTable $table) {
            $table
                ->name(TabsAbbreviationEnum::USERS->value)
                ->pageName(TabsAbbreviationEnum::USERS->value.'Page')
                ->withGlobalSearch()
                ->column(key: 'username', label: __('username'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'parent_type', label: __('type'), canBeHidden: false, sortable: true)
                ->defaultSort('username');
        })->table(function (InertiaTable $table) {
                $table
                    ->withGlobalSearch()
                    ->column(key: 'username', label: __('Username'), canBeHidden: false, sortable: true, searchable: true)
                    ->column(key: 'ip_address', label: __('IP Address'), canBeHidden: false, sortable: true, searchable: true)
                    ->column(key: 'url', label: __('URL'), canBeHidden: false, sortable: true)
                    ->column(key: 'module', label: __('Module'), canBeHidden: false, sortable: true)
                    ->column(key: 'user_agent', label: __('User Agent'), canBeHidden: false, sortable: true)
                    ->column(key: 'location', label: __('location'), canBeHidden: false)
                    ->column(key: 'datetime', label: __('Date & Time'), canBeHidden: false, sortable: true);
            });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request)->withTab(UsersTabsEnum::values());

        return $this->handle();
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
