<?php
/*
 * author Arya Permana - Kirin
 * created on 24-12-2024-16h-13m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\GrpAction;
use App\Actions\SysAdmin\User\WithUsersSubNavigation;
use App\Actions\SysAdmin\WithSysAdminAuthorization;
use App\Http\Resources\History\HistoryResource;
use App\InertiaTable\InertiaTable;
use App\Models\Helpers\Audit;
use App\Models\SysAdmin\User;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexUserAudits extends GrpAction
{
    use WithSysAdminAuthorization;
    use WithUsersSubNavigation;

    public function handle(User $user, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('tags', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $queryBuilder = QueryBuilder::for(Audit::class);

        $queryBuilder->where('auditable_type', 'User')
                        ->where('user_id', $user->id);

        return $queryBuilder
            ->defaultSort('created_at')
            ->allowedSorts(['ip_address','auditable_id', 'auditable_type', 'user_type', 'url','created_at'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix = null, ?array $exportLinks = null): Closure
    {
        return function (InertiaTable $table) use ($exportLinks, $prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withExportLinks($exportLinks)
                ->column(key: 'expand', label: '', type: 'icon')
                ->column(key: 'datetime', label: __('Date'), canBeHidden: false, sortable: true)
                ->column(key: 'user_name', label: __('User'), canBeHidden: false, sortable: true)
                ->column(key: 'old_values', label: __('Old Value'), canBeHidden: false, sortable: true)
                ->column(key: 'new_values', label: __('New Value'), canBeHidden: false, sortable: true)
                ->column(key: 'event', label: __('Action'), canBeHidden: false, sortable: true)
                ->defaultSort('ip_address');
        };
    }

    public function jsonResponse(LengthAwarePaginator $audits): AnonymousResourceCollection
    {
        return HistoryResource::collection($audits);
    }

    public function htmlResponse(LengthAwarePaginator $audits, ActionRequest $request): Response
    {
        $subNavigation = $this->getUsersNavigation($this->group, $request);
        $title = __('Active');
        $model = __('Users');
        $icon  = [
            'icon'  => ['fal', 'fa-user'],
            'title' => __('active users')
        ];
        if ($this->scope == 'suspended') {
            $title = __('Suspended');
            $icon  = [
                'icon'  => ['fal', 'fa-user-slash'],
                'title' => __('suspended users')
            ];
        } elseif ($this->scope == 'all') {
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

                'data'        => HistoryResource::collection($audits),
            ]
        )->table(
            $this->tableStructure(
            )
        );
    }

}
