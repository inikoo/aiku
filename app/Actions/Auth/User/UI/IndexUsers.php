<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\SysAdmin\SysAdminDashboard;
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
                $query->where('user.username', 'LIKE', "%$value%")
                    ->where('user.name', 'LIKE', "%$value%");
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
                    'create' => $this->canEdit && $this->routeName == 'sysadmin.users.index' ? [
                        'route' => [
                            'name'       => 'sysadmin.users.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('user')
                    ] : false,
                ],
                'labels'      => [
                    'usernameNoSet' => __('username no set')
                ],
                'data'        => UserResource::collection($users),


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


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

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
            'sysadmin.users.index'            =>
            array_merge(
                SysAdminDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'=> 'sysadmin.users.index',
                        null
                    ]
                ),
            ),


            default => []
        };
    }

}
