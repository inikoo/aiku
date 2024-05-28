<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 15:27:25 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Actions\Goods\StockFamily\UI\ShowStockFamily;
use App\Actions\OrgAction;
use App\Actions\UI\Inventory\ShowInventoryDashboard;
use App\Http\Resources\Inventory\OrgStocksResource;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\OrgStockFamily;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexOrgStocks extends OrgAction
{
    private OrgStockFamily|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("inventory.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("inventory.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $this->parent = $organisation;

        return $this->handle(parent: $organisation);
    }

    public function inStockFamily(Organisation $organisation, OrgStockFamily $orgStockFamily, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        $this->parent = $orgStockFamily;

        return $this->handle(parent: $orgStockFamily);
    }


    public function handle(OrgStockFamily|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('stocks.code', $value)
                    ->orWhereAnyWordStartWith('stocks.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(OrgStock::class);

        /*
         foreach ($this->elementGroups as $key => $elementGroup) {
             $queryBuilder->whereElementGroup(
                 prefix: $prefix,
                 key: $key,
                 allowedElements: array_keys($elementGroup['elements']),
                 engine: $elementGroup['engine']
             );
         }
        */


        return $queryBuilder
            ->defaultSort('stocks.code')
            ->select([
                'stock_families.slug as family_slug',
                'stock_families.code as family_code',
                'stocks.code',
                'stocks.name',
                'stocks.slug',
                'stocks.description',
                'stocks.unit_value',
                'number_locations',
                'quantity_in_locations'
            ])
            ->leftJoin('stocks', 'org_stocks.stock_id', 'stocks.id')
            ->leftJoin('org_stock_stats', 'org_stock_stats.org_stock_id', 'org_stocks.id')
            ->leftJoin('org_stock_families', 'org_stock_families.id', 'org_stocks.org_stock_family_id')
            ->leftJoin('stock_families', 'stock_families.id', 'org_stock_families.stock_family_id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'StockFamily') {
                    $query->where('org_stocks.org_stock_family_id', $parent->id);
                } elseif (class_basename($parent) == 'Organisation') {
                    $query->where('org_stocks.organisation_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'family_code', 'description', 'unit_value'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
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
                        ],
                        'StockFamily' => [
                            'title'       => __("No SKUs found"),
                            'description' => $this->canEdit ? __('Get started by creating a new SKU. ✨')
                                : null,
                            'count'       => $parent->stats->number_stocks,
                        ],
                        default => null
                    }
                )
                ->column(key: 'code', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'family_code', label: __('family'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'unit_value', label: __('unit value'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'stock', label: __('stock'), canBeHidden: false, sortable: true, searchable: true);

        };
    }


    public function jsonResponse(LengthAwarePaginator $stocks): AnonymousResourceCollection
    {
        return OrgStocksResource::collection($stocks);
    }

    public function htmlResponse(LengthAwarePaginator $stocks, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $container = null;
        if (class_basename($scope) == 'StockFamily') {
            $container = [
                'icon'    => ['fal', 'fa-boxes-alt'],
                'tooltip' => __('Stock Family'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'Org/Inventory/OrgStocks',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __("SKUs"),
                'pageHead'    => [
                    'title'      => __("SKUs"),
                    'container'  => $container,
                    'icon'  => [
                        'icon'  => ['fal', 'fa-box'],
                        'title' => __('SKU')
                    ],
                    'actions_xx' => [
                        $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new SKU'),
                            'label'   => __('SKU'),
                            'route'   => match ($request->route()->getName()) {
                                'grp.org.inventory.org-stock-families.show.stocks.index' => [
                                    'name'       => 'inventory.stock-families.show.stocks.create',
                                    'parameters' => array_values($request->route()->originalParameters())
                                ],
                                default => [
                                    'name'       => 'inventory.stocks.create',
                                    'parameters' => array_values($request->route()->originalParameters())
                                ]
                            }
                        ] : false,
                    ]
                ],
                'data'        => OrgStocksResource::collection($stocks),

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
            'grp.org.inventory.org-stocks.index' =>
            array_merge(
                ShowInventoryDashboard::make()->getBreadcrumbs($routeParameters),
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
