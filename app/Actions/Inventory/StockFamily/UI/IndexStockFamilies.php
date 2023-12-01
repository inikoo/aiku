<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 13:21:43 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Http\Resources\Inventory\StockFamilyResource;
use App\Models\Inventory\StockFamily;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexStockFamilies extends InertiaAction
{
    use HasUIStockFamilies;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('inventory.stocks.edit');

        return
            (

                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.stocks.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(app('currentTenant'));
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('stock_families.code', 'LIKE', "$value%")
                    ->orWhere('stock_families.name', 'LIKE', "%$value%");
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(StockFamily::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

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

    public function tableStructure($parent, $prefix=null): Closure
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
                        'count'       => app('currentTenant')->inventoryStats->number_stock,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new stock family'),
                            'label'   => __('stock family'),
                            'route'   => [
                                'name'       => 'grp.oms.stock-families.create',
                                'parameters' => array_values($this->originalParameters)
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
        return StockFamilyResource::collection($stocks);
    }

    public function htmlResponse(LengthAwarePaginator $stockFamily, ActionRequest $request): Response
    {

        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        return Inertia::render(
            'Inventory/StockFamilies',
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
                        $this->canEdit && $request->route()->getName() == 'grp.oms.stock-families.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new SKU family'),
                            'label'   => __('SKU family'),
                            'route'   => [
                                'name'       => 'grp.oms.stock-families.create',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false,
                    ]
                ],
                'data' => StockFamilyResource::collection($stockFamily),
            ]
        )->table($this->tableStructure($parent));
    }

    public function getBreadcrumbs($suffix = null): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'grp.oms.stock-families.index'
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
