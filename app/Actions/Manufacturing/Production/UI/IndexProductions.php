<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 19:25:21 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Production\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Manufacturing\ManufacturingDashboard;
use App\Http\Resources\Manufacturing\ProductionsResource;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexProductions extends OrgAction
{
    private Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("productions.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("productions.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function handle(Organisation $organisation, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('productions.name', $value)
                    ->orWhereStartWith('productions.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Production::class);

        $queryBuilder->where('organisation_id', $organisation->id);


        return $queryBuilder
            ->defaultSort('productions.code')
            ->select([
                'productions.code as code',
                'productions.id',
                'productions.name',
                'productions.slug as slug'
            ])
            ->leftJoin('production_stats', 'production_stats.production_id', 'productions.id')
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('no manufacturing plant'),
                        'description' => $this->canEdit ? __('Get started set up your new production plant.') : null,
                        'count'       => $parent->manufactureStats->number_productions,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new production plant'),
                            'label'   => __('manufacturing plant'),
                            'route'   => [
                                'name'       => 'grp.org.productions.create',
                                'parameters' => $parent->slug
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $productions): AnonymousResourceCollection
    {
        return ProductionsResource::collection($productions);
    }


    public function htmlResponse(LengthAwarePaginator $productions, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Production/Productions',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('productions'),
                'pageHead'    => [
                    'title'   => __('productions'),
                    'icon'    => [
                        'title' => __('productions'),
                        'icon'  => 'fal fa-production'
                    ],
                    'actions' => [
                        $this->canEdit && $request->route()->routeName == 'grp.org.manufacturing.productions.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new production'),
                            'label'   => __('production'),
                            'route'   => [
                                'name'       => 'grp.org.productions.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ]
                ],
                'data'        => ProductionsResource::collection($productions),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return array_merge(
            (new ManufacturingDashboard())->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.manufacturing.productions.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('productions'),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }
}
