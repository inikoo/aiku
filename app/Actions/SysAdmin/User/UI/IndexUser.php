<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 10:55:21 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\SysAdmin\User;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexUser extends InertiaAction
{
    use HasUIUsers;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('user.username', 'LIKE', "%$value%")
                    ->where('user.name', 'LIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::USER->value);
        return QueryBuilder::for(User::class)
            ->defaultSort('username')
            ->select([
                'username',
                'users.id',
                'parent_type',
                'parent_id'
            ])
            ->allowedSorts(['username', 'email', 'parent_type'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::USER->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::USER->value)
                ->pageName(TabsAbbreviationEnum::USER->value.'Page');
            $table
                ->withGlobalSearch()
                ->column(key: 'username', label: __('username'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
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


    public function htmlResponse(LengthAwarePaginator $users, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        return Inertia::render(
            'SysAdmin/Users',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('users'),
                'pageHead'    => [
                    'title'   => __('users'),
                    'create'  => $this->canEdit && $this->routeName=='sysadmin.users.index' ? [
                        'route' => [
                            'name'       => 'sysadmin.users.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('user')
                    ] : false,
                ],
                'labels'      => [
                    'usernameNoSet' => __('username no set')
                ],
                'users'       => JsonResource::collection($users),


            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle();
    }
}
