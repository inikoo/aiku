<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Actions\GrpAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Http\Resources\SysAdmin\Organisation\OrganisationsResource;
use App\InertiaTable\InertiaTable;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexOrganisations extends GrpAction
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


    public function handle(Group $group, $prefix = null): LengthAwarePaginator
    {
        $this->group  = $group;
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('name', $value)->whereStartWith('code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Organisation::class);
        $queryBuilder->where('group_id', $group->id);
        $queryBuilder->where('type', '!=', OrganisationTypeEnum::AGENT);

        foreach ($this->getElementGroups() as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('code')
            ->select(['name', 'slug', 'type','code'])
            ->allowedSorts([ 'name','type','code'])
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
                        'title'       => __('no organisation'),
                        'description' => $this->canEdit ? __('Get started by creating a new organisation.') : null,
                        'count'       => $group->number_organisations,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new organisation'),
                            'label'   => __('organisation'),
                            'route'   => [
                                'name'       => 'grp.organisations.create',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'type', label: '', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('sysadmin.edit');
        return  $request->user()->hasPermissionTo('sysadmin.view');
    }


    public function jsonResponse(LengthAwarePaginator $organisations): AnonymousResourceCollection
    {
        return OrganisationsResource::collection($organisations);
    }


    public function htmlResponse(LengthAwarePaginator $organisations): Response
    {
        return Inertia::render(
            'Organisations/Organisations',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('organisations'),
                'pageHead'    => [
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
                'data'        => OrganisationsResource::collection($organisations),
            ]
        )->table($this->tableStructure($this->group));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(app('group'), $request);

        return $this->handle($this->group);
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
                                'name' => 'grp.organisations.index'
                            ],
                            'label'  => __('organisations'),
                        ]
                    ]
                ]
            );
    }


}
