<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 20:07:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\SysAdmin\ShowSysAdminDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\SysAdmin\UserInertiaResource;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexUser
{
    use AsAction;
    use WithInertia;


    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('user.username', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(User::class)
            ->with(['parent' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                                        Employee::class => ['name'],
                                        Guest::class => ['name'],
                                    ]);
            }])
            ->defaultSort('username')
            ->select(['username', 'users.id', 'parent_type', 'parent_id'])
            ->allowedSorts(['username', 'email', 'name', 'parent_type'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? 15)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
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


    public function htmlResponse(LengthAwarePaginator $users)
    {
        return Inertia::render(
            'SysAdmin/Users',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('users'),
                'pageHead'    => [
                    'title' => __('users'),
                ],
                'labels'      => [
                    'usernameNoSet' => __('username no set')
                ],
                'users'       => UserInertiaResource::collection($users),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'username', label: __('username'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'parent_type', label: __('type'), canBeHidden: false, sortable: true)
                ->defaultSort('username');
        });
    }


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowSysAdminDashboard())->getBreadcrumbs(),
            [
                'sysadmin.users.index' => [
                    'route' => 'sysadmin.users.index',
                    'modelLabel' => [
                        'label' => __('users')
                    ],
                ],
            ]
        );
    }


}
