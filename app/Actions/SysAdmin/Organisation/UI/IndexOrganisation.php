<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\SysAdmin\Organisation\OrganisationResource;
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
use Spatie\QueryBuilder\QueryBuilder;

class IndexOrganisation extends InertiaAction
{
    /**
     * @var \App\Models\SysAdmin\Group
     */
    private Group $group;

    protected function getElementGroups(): void
    {
        $this->elementGroups =
            [
                'status' => [
                    'label'    => __('Status'),
                    'elements' => ['active' => __('Active'), 'suspended' => __('Suspended')],
                    'engine'   => function ($query, $elements) {
                        $query->where('users.status', array_pop($elements) === 'active');
                    }

                ]
            ];
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(Group $group, $prefix = null): LengthAwarePaginator
    {
        $this->group  = $group;
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Organisation::class);

        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('slug')
            ->select(['name', 'slug', 'email'])
            ->allowedSorts(['slug', 'name', 'email'])
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
                                'name'       => 'grp.sysadmin.guests.create',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'email', label: __('email'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('sysadmin.guests.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('sysadmin.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $organisations): AnonymousResourceCollection
    {
        return OrganisationResource::collection($organisations);
    }


    public function htmlResponse(LengthAwarePaginator $organisations): Response
    {
        return Inertia::render(
            'Org/Organisations',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('organisation'),
                'pageHead'    => [
                    'title'   => __('organisation'),
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('organisation'),
                            'route' => [
                                'name'       => 'grp.sysadmin.guests.create',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ]
                        ] : false
                    ]
                ],
                'data'        => OrganisationResource::collection($organisations),
            ]
        )->table($this->tableStructure($this->group));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(app('group'));
    }

    public function getBreadcrumbs($suffix = null): array
    {
        return [];
    }


}
