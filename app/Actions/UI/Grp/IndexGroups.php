<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp;

use App\Actions\GrpAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Http\Resources\SysAdmin\Group\GroupResource;
use App\InertiaTable\InertiaTable;
use App\Models\SysAdmin\Group;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexGroups extends GrpAction
{
    protected function getElementGroups(): array
    {
        return   [
                'status' => [
                    'label'    => __('Type'),
                    'elements' => ['active' => __('Active'), 'suspended' => __('Suspended')],
                    'engine'   => function ($query, $elements) {
                        $query->where('users.status', array_pop($elements) === 'active');
                    }

                ]
            ];
    }


    public function handle($prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Group::class);
        $queryBuilder->with('currency');

        foreach ($this->getElementGroups() as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('groups.name')
            ->allowedSorts(['groups.name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'slug', label: __('slug'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'subdomain', label: __('subdomain'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('name');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('sysadmin.edit');
        return  $request->user()->hasPermissionTo('sysadmin.view');
    }


    public function jsonResponse(LengthAwarePaginator $groups): AnonymousResourceCollection
    {
        return GroupResource::collection($groups);
    }


    public function htmlResponse(LengthAwarePaginator $groups): Response
    {
        return Inertia::render(
            'Groups',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('organisations'),
                'pageHead'    => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-building'],
                        'title' => __('organisations')
                    ],
                    'title'   => __('organisations'),
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('organisation'),
                            'route' => [
                                'name'       => 'grp.organisations.create',
                                'parameters' => []
                            ]
                        ] : false
                    ]
                ],
                'data'        => GroupResource::collection($groups),
            ]
        )->table($this->tableStructure());
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(app('group'), $request);

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.index'
                            ],
                            'label'  => __('Groups'),
                        ]
                    ]
                ]
            );
    }


}
