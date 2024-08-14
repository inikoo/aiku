<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest\UI;

use App\Actions\GrpAction;
use App\Actions\SysAdmin\WithSysAdminAuthorization;
use App\Actions\UI\Grp\SysAdmin\ShowSysAdminDashboard;
use App\Http\Resources\SysAdmin\GuestsResource;
use App\InertiaTable\InertiaTable;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexGuests extends GrpAction
{
    use WithSysAdminAuthorization;


    protected function getElementGroups(): array
    {
        return [
            'status' => [
                'label'    => __('Status'),
                'elements' => ['active' => __('Active'), 'suspended' => __('Suspended')],
                'engine'   => function ($query, $elements) {
                    $query->where('users.status', array_pop($elements) === 'active');
                }

            ]
        ];
    }


    public function handle(Group $group, $prefix = null): LengthAwarePaginator
    {
        $this->group  = $group;
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('guests.contact_name', $value)
                    ->orWhereStartWith('guests.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Guest::class)
            ->leftJoin(
                'users',
                function ($leftJoin) {
                    $leftJoin
                        ->on('users.parent_id', '=', 'guests.id')
                        ->where('users.parent_type', '=', 'Guest');
                }
            )->leftJoin('user_stats', 'user_stats.user_id', 'users.id');
        foreach ($this->getElementGroups() as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('guests.code')
            ->select(['guests.id', 'guests.slug', 'guests.code', 'guests.contact_name', 'guests.email', 'number_logins', 'last_login_at', 'number_failed_logins', 'last_failed_login_at'])
            ->allowedSorts(['code', 'contact_name', 'email', 'number_logins', 'last_login_at', 'number_failed_logins', 'last_failed_login_at'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Group $group, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix, $group) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('no guest'),
                        'description' => $this->canEdit ? __('Get started by creating a new guest.') : null,
                        'count'       => $group->sysadminStats->number_guests_status_active,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new guest'),
                            'label'   => __('guest'),
                            'route'   => [
                                'name'       => 'grp.sysadmin.guests.create',
                                'parameters' => []
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'contact_name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'email', label: __('email'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }


    public function jsonResponse(LengthAwarePaginator $guests): AnonymousResourceCollection
    {
        return GuestsResource::collection($guests);
    }


    public function htmlResponse(LengthAwarePaginator $guests, ActionRequest $request): Response
    {
        return Inertia::render(
            'SysAdmin/Guests',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('guests'),
                'pageHead'    => [
                    'title'   => __('guests'),
                    'actions' => [
                        $this->canEdit && $request->route()->getName() == 'grp.sysadmin.guests.index' ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('guest'),
                            'route' => [

                                'name'       => 'grp.sysadmin.guests.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]

                        ] : false
                    ]
                ],
                'data'        => GuestsResource::collection($guests),
            ]
        )->table($this->tableStructure($this->group));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(app('group'), $request);

        return $this->handle($this->group);
    }

    public function getBreadcrumbs($suffix = null): array
    {
        return array_merge(
            ShowSysAdminDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'grp.sysadmin.guests.index',
                        ],
                        'label' => __('Guests'),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }


}
