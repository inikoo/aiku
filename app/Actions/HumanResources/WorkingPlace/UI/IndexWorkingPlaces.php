<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:12:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\WorkingPlace\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\UI\WorkplaceTabsEnum;
use App\Http\Resources\HumanResources\WorkPlaceInertiaResource;
use App\Http\Resources\HumanResources\WorkPlaceResource;
use App\InertiaTable\InertiaTable;
use App\Models\HumanResources\Workplace;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexWorkingPlaces extends InertiaAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('workplaces.name', 'ILIKE', "%$value%")
                    ->orWhere('workplaces.slug', 'ILIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Workplace::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            /** @noinspection PhpUndefinedMethodInspection */
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('slug')
            ->select(['slug', 'id', 'name', 'type'])
            ->allowedSorts(['slug','name'])
            ->allowedFilters([$globalSearch, 'slug', 'name', 'type'])
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
                ->withEmptyState(
                    [
                        'title'       => __('no working places'),
                        'description' => $this->canEdit ? __('Get started by creating a new working place.') : null,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new working place'),
                            'label'   => __('working place'),
                            'route'   => [
                                'name'       => 'grp.hr.working-places.create',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('hr.working-places.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('hr.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $workplace): AnonymousResourceCollection
    {
        return WorkPlaceResource::collection($workplace);
    }


    public function htmlResponse(LengthAwarePaginator $workplace): Response
    {

        return Inertia::render(
            'HumanResources/WorkingPlaces',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('working places'),
                'pageHead'    => [
                    'title'  => __('working places'),
                    'actions'=> [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('working place'),
                            'route' => [
                                'name'       => 'grp.hr.working-places.create',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false
                    ]
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => WorkplaceTabsEnum::navigation(),
                ],
                'data'        => WorkPlaceInertiaResource::collection($workplace),
            ]
        )->table($this->tableStructure());
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle();
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'grp.hr.working-places.index'
                        ],
                        'label' => __('working places'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
