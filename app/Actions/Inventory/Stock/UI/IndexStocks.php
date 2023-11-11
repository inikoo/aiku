<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 15:27:25 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\StockFamily\UI\IndexStockFamilies;
use App\Actions\Inventory\StockFamily\UI\ShowStockFamily;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use App\Models\Organisation\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexStocks extends InertiaAction
{
    private StockFamily|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.stocks.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.stocks.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent    = app('currentTenant');
        return $this->handle(parent:  app('currentTenant'));
    }

    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = $stockFamily;
        return $this->handle(parent:  $stockFamily);
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(StockFamily|Organisation $parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('stocks.code', 'LIKE', "$value%")
                    ->orWhere('stocks.name', 'LIKE', "%$value%")
                    ->orWhere('stocks.description', 'LIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Stock::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('stocks.code')
            ->select([
                'stock_families.slug as family_slug',
                'stock_families.code as family_code',
                'stocks.code',
                'stocks.slug',
                'stocks.description',
                'stocks.unit_value',
                'number_locations',
                'quantity_in_locations'])
            ->leftJoin('stock_stats', 'stock_stats.stock_id', 'stocks.id')
            ->leftJoin('stock_families', 'stock_families.id', 'stocks.stock_family_id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'StockFamily') {
                    $query->where('stocks.stock_family_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'family_code','description', 'unit_value'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->defaultSort('slug')
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No SKUs found"),
                            'description' => $this->canEdit && $parent->stats->number_stock_families == 0 ? __('Get started by creating a shop. ✨')
                                : __("In fact, is no even create a SKUs family yet 🤷🏽‍♂️"),
                            'count'       => $parent->stats->number_stocks,
                            'action'      => $this->canEdit && $parent->stats->number_stock_families == 0 ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new SKUs family'),
                                'label'   => __('SKUs family'),
                                'route'   => [
                                    'name'       => 'inventory.families.create',
                                    'parameters' => array_values($this->originalParameters)
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
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            ] : null
                        ],
                        default => null
                    }
                )
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'family_code', label: __('family'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'description', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'unit_value', label: __('unit value'), canBeHidden: false, sortable: true, searchable: true);
        };
    }



    public function jsonResponse(LengthAwarePaginator $stocks): AnonymousResourceCollection
    {
        return StockResource::collection($stocks);
    }

    public function htmlResponse(LengthAwarePaginator $stocks, ActionRequest $request): Response
    {

        $parent       = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        $this->parent = $parent;
        $scope        = $parent;
        $container    =null;
        if (class_basename($scope) == 'StockFamily') {
            $container = [
                'icon'    => ['fal', 'fa-boxes-alt'],
                'tooltip' => __('Stock Family'),
                'label'   => Str::possessive($scope->name)
            ];
        }
        return Inertia::render(
            'Inventory/Stocks',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __("SKUs"),
                'pageHead'    => [
                    'title'        => __("SKUs"),
                    'container'    => $container,
                    'iconRight'    => [
                        'icon'  => ['fal', 'fa-box'],
                        'title' => __('SKU')
                    ],
                    'actions'=> [
                        $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new SKU'),
                            'label'   => __('SKU'),
                            'route'   => match ($this->routeName) {
                                'inventory.stock-families.show.stocks.index' => [
                                    'name'       => 'inventory.stock-families.show.stocks.create',
                                    'parameters' => array_values($this->originalParameters)
                                ],
                                default => [
                                    'name'       => 'inventory.stocks.create',
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            }
                        ] : false,
                    ]
                ],
                'data'  => StockResource::collection($stocks),

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
            'inventory.stocks.index' =>
            array_merge(
                IndexStockFamilies::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            'inventory.stock-families.show.stocks.index' =>
            array_merge(
                ShowStockFamily::make()->getBreadcrumbs($routeParameters['stockFamily']),
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
