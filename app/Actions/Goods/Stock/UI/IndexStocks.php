<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock\UI;

use App\Actions\Goods\StockFamily\UI\ShowStockFamily;
use App\Actions\GrpAction;
use App\Actions\UI\Goods\ShowGoodsDashboard;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Http\Resources\Goods\StocksResource;
use App\InertiaTable\InertiaTable;
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

class IndexStocks extends GrpAction
{
    private StockFamily|Group $parent;
    private string $bucket;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("goods.{$this->group->id}.edit");

        return $request->user()->hasPermissionTo("goods.{$this->group->id}.view");
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->parent = group();
        $this->initialisation($this->parent, $request);

        return $this->handle($this->parent);
    }

    public function active(ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'active';
        $this->parent = group();
        $this->initialisation($this->parent, $request);

        return $this->handle($this->parent);
    }

    public function inProcess(ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'in_process';
        $this->parent = group();
        $this->initialisation($this->parent, $request);

        return $this->handle($this->parent);
    }

    public function discontinuing(ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'discontinuing';
        $this->parent = group();
        $this->initialisation($this->parent, $request);

        return $this->handle($this->parent);
    }

    public function discontinued(ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'discontinued';
        $this->parent = group();
        $this->initialisation($this->parent, $request);

        return $this->handle($this->parent);
    }

    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(group(), $request);
        $this->parent = $stockFamily;

        return $this->handle(parent: $stockFamily);
    }

    protected function getElementGroups(Group|StockFamily $parent): array
    {
        return [
            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    StockStateEnum::labels(),
                    StockStateEnum::count($parent)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }


    public function handle(StockFamily|Group $parent, $prefix = null): LengthAwarePaginator
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

        $queryBuilder = QueryBuilder::for(Stock::class);

        if ($parent instanceof StockFamily) {
            $queryBuilder->where('stock_family_id', $parent->id);
        } else {
            $queryBuilder->where('stocks.group_id', $this->group->id);
        }


        if ($this->bucket == 'active') {
            $queryBuilder->where('stocks.state', StockStateEnum::ACTIVE);
        } elseif ($this->bucket == 'discontinuing') {
            $queryBuilder->where('stocks.state', StockStateEnum::DISCONTINUING);
        } elseif ($this->bucket == 'discontinued') {
            $queryBuilder->where('stocks.state', StockStateEnum::DISCONTINUED);
        } elseif ($this->bucket == 'in_process') {
            $queryBuilder->where('stocks.state', StockStateEnum::IN_PROCESS);
        } else {
            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }


        return $queryBuilder
            ->defaultSort('stocks.code')
            ->select([
                'stock_families.slug as family_slug',
                'stock_families.code as family_code',
                'stocks.code',
                'stocks.slug',
                'stocks.name',
                'stocks.unit_value',
            ])
            ->leftJoin('stock_stats', 'stock_stats.stock_id', 'stocks.id')
            ->leftJoin('stock_families', 'stock_families.id', 'stocks.stock_family_id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'StockFamily') {
                    $query->where('stocks.stock_family_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'family_code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Group|StockFamily $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }

            $table
                ->defaultSort('slug')
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Group' => [
                            'title'       => __("No SKUs found"),
                            'description' => $this->canEdit && $parent->inventoryStats->number_stock_families == 0 ? __('Get started by creating a shop. ✨')
                                : __("In fact, is no even create a SKUs family yet 🤷🏽‍♂️"),
                            'count'       => $parent->inventoryStats->number_stocks,
                            'action'      => $this->canEdit && $parent->inventoryStats->number_stock_families == 0 ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new SKUs family'),
                                'label'   => __('SKUs family'),
                                'route'   => [
                                    'name'       => 'grp.goods.stock-families.create',
                                    'parameters' => []
                                ]
                            ] : null
                        ],
                        'StockFamily' => [
                            'title'       => __("No SKUs found"),
                            'description' => $this->canEdit ? __('Get started by creating a new SKU. ✨')
                                : null,
                            'count'       => $parent->stats->number_stocks,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new SKU'),
                                'label'   => __('SKU'),
                                'route'   => [
                                    'name'       => 'inventory.stock-families.show.stocks.create',
                                    'parameters' => [$parent->slug]
                                ]
                            ] : null
                        ],
                        default => null
                    }
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'family_code', label: __('family'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }


    public function jsonResponse(LengthAwarePaginator $stocks): AnonymousResourceCollection
    {
        return StocksResource::collection($stocks);
    }


    public function getStocksSubNavigation(ActionRequest $request): array
    {
        return [

            [
                'label' => __('Active'),
                'href'  => [
                    'name'       => 'grp.goods.active_stocks.index',
                    'parameters' => []
                ]
            ],
            [
                'label' => __('In process'),
                'href'  => [
                    'name'       => 'grp.goods.in_process_stocks.index',
                    'parameters' => []
                ]
            ],
            [
                'label' => __('Discounting'),
                'href'  => [
                    'name'       => 'grp.goods.discontinuing_stocks.index',
                    'parameters' => []
                ]
            ],
            [
                'label' => __('Discontinued'),
                'href'  => [
                    'name'       => 'grp.goods.discontinued_stocks.index',
                    'parameters' => []
                ]
            ],
            [
                'label' => __('All SKUs'),
                'icon'  => 'fal fa-bars',
                'href'  => [
                    'name'       => 'grp.goods.stocks.index',
                    'parameters' => []
                ]
            ],

        ];
    }


    public function htmlResponse(LengthAwarePaginator $stocks, ActionRequest $request): Response
    {
        $subNavigation = $this->getStocksSubNavigation($request);

        $title = match ($this->bucket) {
            'active'        => __('Active SKUs'),
            'in_process'    => __('In process SKUs'),
            'discontinuing' => __('Discontinuing SKUs'),
            'discontinued'  => __('Discontinued SKUs'),
            default         => __('SKUs')
        };

        return Inertia::render(
            'Goods/Stocks',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => $title,
                'pageHead'    => [
                    'title'         => $title,
                    'iconRight'     => [
                        'icon'  => ['fal', 'fa-box'],
                        'title' => __('SKU')
                    ],
                    'actions'       => [
                        $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new SKU'),
                            'label'   => __('SKU'),
                            'route'   => match ($request->route()->getName()) {
                                'inventory.stock-families.show.stocks.index' => [
                                    'name'       => 'inventory.stock-families.show.stocks.create',
                                    'parameters' => array_values($request->route()->originalParameters())
                                ],
                                default => [
                                    'name'       => 'inventory.stocks.create',
                                    'parameters' => array_values($request->route()->originalParameters())
                                ]
                            }
                        ] : false,
                    ],
                    'subNavigation' => $subNavigation
                ],
                'data'        => StocksResource::collection($stocks),

            ]
        )->table($this->tableStructure($this->parent));
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('SKUs'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'grp.goods.stocks.index' =>
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

            'grp.goods.stock-families.show.stocks.index' =>
            array_merge(
                ShowStockFamily::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            default => []
        };
    }
}
