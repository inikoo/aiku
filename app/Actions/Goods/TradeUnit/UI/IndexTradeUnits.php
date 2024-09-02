<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\TradeUnit\UI;

use App\Actions\Goods\HasGoodsAuthorisation;
use App\Actions\Goods\StockFamily\UI\ShowStockFamily;
use App\Actions\GrpAction;
use App\Actions\UI\Goods\ShowGoodsDashboard;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Http\Resources\Goods\StocksResource;
use App\Http\Resources\Goods\TradeUnitsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Goods\TradeUnit;
use App\Models\SupplyChain\Stock;
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

class IndexTradeUnits extends GrpAction
{
    use HasGoodsAuthorisation;

    private Group $parent;
    private string $bucket;


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->initialisation($this->parent, $request);

        return $this->handle($this->parent);
    }

    public function handle(Group $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('stocks.code', $value)
                    ->orWhereStartWith('stock_families.code', $value)
                    ->orWhereAnyWordStartWith('stocks.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(TradeUnit::class);
        $queryBuilder->where('trade_units.group_id', $this->group->id);


        $queryBuilder
            ->defaultSort('trade_units.code')
            ->select([
                'trade_units.code',
                'trade_units.slug',
                'trade_units.name',
                'trade_units.description',
                'trade_units.gross_weight',
                'trade_units.net_weight',
                'trade_units.dimensions',
                'trade_units.volume',
                'trade_units.type'
            ]);


        return $queryBuilder->allowedSorts(['code', 'type', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Group $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->defaultSort('code')
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Group' => [
                            'title'       => __("No Trade Units found"),
                        ],
                        default => null
                    }
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'net_weight', label: __('weight'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true);
        };
    }


    public function jsonResponse(LengthAwarePaginator $tradeUnit): AnonymousResourceCollection
    {
        return TradeUnitsResource::collection($tradeUnit);
    }

    public function htmlResponse(LengthAwarePaginator $tradeUnit, ActionRequest $request): Response
    {
        return Inertia::render(
            'Goods/TradeUnits',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => 'Trade Units',
                'pageHead'    => [
                    'title'         => 'Trade Units',
                    'iconRight'     => [
                        'icon'  => ['fal', 'fa-atom'],
                        'title' => __('Trade Units')
                    ],
                    // 'actions'       => [
                    //     $this->canEdit ? [
                    //         'type'    => 'button',
                    //         'style'   => 'create',
                    //         'tooltip' => __('new SKU'),
                    //         'label'   => __('SKU'),
                    //         'route'   => match ($request->route()->getName()) {
                    //             'inventory.stock-families.show.stocks.index' => [
                    //                 'name'       => 'inventory.stock-families.show.stocks.create',
                    //                 'parameters' => array_values($request->route()->originalParameters())
                    //             ],
                    //             default => [
                    //                 'name'       => 'inventory.stocks.create',
                    //                 'parameters' => array_values($request->route()->originalParameters())
                    //             ]
                    //         }
                    //     ] : false,
                    // ],
                ],
                'data'        => TradeUnitsResource::collection($tradeUnit),

            ]
        )->table($this->tableStructure(parent: $this->parent));
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Trade Units'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'grp.goods.trade-units.index'
            =>
            array_merge(
                ShowGoodsDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => []
                    ],
                    $suffix
                )
            ),

            default => []
        };
    }
}
