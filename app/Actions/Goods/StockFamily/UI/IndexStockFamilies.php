<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\StockFamily\UI;

use App\Actions\Goods\HasGoodsAuthorisation;
use App\Actions\GrpAction;
use App\Actions\UI\Goods\ShowGoodsDashboard;
use App\Http\Resources\Goods\StockFamiliesResource;
use App\InertiaTable\InertiaTable;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexStockFamilies extends GrpAction
{
    use HasGoodsAuthorisation;

    public function asController(ActionRequest $request): LengthAwarePaginator
    {

        $this->initialisation(group(), $request);

        return $this->handle($this->group);
    }

    public function handle(Group $group, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('stock_families.code', $value)
                    ->orWhereAnyWordStartWith('stock_families.name', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(StockFamily::class);
        $queryBuilder->where('stock_families.group_id', $group->id);
        /*
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }
        */

        return $queryBuilder
            ->defaultSort('code')
            ->select([
                'slug',
                'code',
                'stock_families.id as id',
                'name',
                'number_stocks'
            ])
            ->leftJoin('stock_family_stats', 'stock_family_stats.stock_family_id', 'stock_families.id')
            ->allowedSorts(['code', 'name', 'number_stocks'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Group $parent, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('no stock families'),
                        'description' => $this->canEdit ? __('Get started by creating a new stock family.') : null,
                        'count'       => $parent->inventoryStats->number_stocks,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new stock family'),
                            'label'   => __('stock family'),
                            'route'   => [
                                'name'       => 'grp.goods.stock-families.create',
                                'parameters' => []
                            ]
                        ] : null
                    ]
                )
                ->column(key: 'code', label: 'code', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_stocks', label: 'SKUs', canBeHidden: false, sortable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $stocks): AnonymousResourceCollection
    {
        return StockFamiliesResource::collection($stocks);
    }

    public function htmlResponse(LengthAwarePaginator $stockFamily, ActionRequest $request): Response
    {

        $parent = $this->group;
        return Inertia::render(
            'Goods/StockFamilies',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __("SKUs families"),
                'pageHead'    => [
                    'title'   => __("SKUs families"),
                    'icon'    => [
                        'title' => __("SKUs families"),
                        'icon'  => 'fal fa-boxes-alt'
                    ],
                    'actions'=> [
                        $this->canEdit && $request->route()->getName() == 'grp.goods.stock-families.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new SKU family'),
                            'label'   => __('SKU family'),
                            'route'   => [
                                'name'       => 'grp.goods.stock-families.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ]
                ],
                'data' => StockFamiliesResource::collection($stockFamily),
            ]
        )->table($this->tableStructure($parent));
    }

    public function getBreadcrumbs($suffix = null): array
    {
        return array_merge(
            ShowGoodsDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'grp.goods.stock-families.index'
                        ],
                        'label' => __("SKUs families"),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }
}
