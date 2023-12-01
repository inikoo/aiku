<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Product\UI;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\IndexShops;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Http\Resources\Market\ProductResource;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
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

class IndexProducts extends InertiaAction
{
    private Shop|ProductCategory|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('shops.products.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.products.view')
            );
    }

    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = app('currentTenant');
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = $shop;
        return $this->handle($shop);
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(Shop|ProductCategory|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('products.name', $value)
                    ->orWhere('products.slug', 'ilike', "$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Product::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('products.slug')
            ->select([
                'products.code',
                'products.name',
                'products.state',
                'products.created_at',
                'products.updated_at',
                'products.slug',
                'shops.slug as shop_slug'
            ])
            ->leftJoin('product_stats', 'products.id', 'product_stats.product_id')
            ->leftJoin('shops', 'products.shop_id', 'shops.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('products.shop_id', $parent->id);
                } elseif (class_basename($parent) == 'Shop') {
                    $query->where('families.department_id', $parent->id);
                }
            })
            ->allowedSorts(['slug', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Shop|ProductCategory|Organisation $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No products found"),
                            'description' => $this->canEdit && $parent->marketStats->number_shops==0 ? __('Get started by creating a new shop. ✨')
                                : __("In fact, is no even a shop yet 🤷🏽‍♂️"),
                            'count'       => $parent->marketStats->number_products,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new shop'),
                                'label'   => __('shop'),
                                'route'   => [
                                    'name'       => 'shops.create',
                                    'parameters' => array_values($this->originalParameters)
                                ]
                            ] : null
                        ],
                        'Shop' => [
                            'title'       => __("No products found"),
                            'count'       => $parent->stats->number_products,
                        ],
                        default => null
                    }

                    /*
                    [
                        'title'       => __('no products'),
                        'description' => $this->canEdit ? __('Get started by creating a new product.') : null,
                        'count'       => app('currentTenant')->stats->number_products,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new product'),
                            'label'   => __('product'),
                            'route'   => [
                                'name'       => 'shops.products.create',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : null
                    ]*/
                )
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $products): AnonymousResourceCollection
    {
        return ProductResource::collection($products);
    }

    public function htmlResponse(LengthAwarePaginator $products, ActionRequest $request): Response
    {
        $scope    =$this->parent;
        $container=null;
        if (class_basename($scope) == 'Shop') {
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }



        return Inertia::render(
            'Market/Products',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('Products'),
                'pageHead'    => [
                    'title'        => __('products'),
                    'container'    => $container,
                    'iconRight'    => [
                        'icon'  => ['fal', 'fa-cube'],
                        'title' => __('product')
                    ],
                    'actions' => [
                        $this->canEdit && class_basename($this->parent)=='ProductCategory' && $this->parent->is_family ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new product'),
                            'label'   => __('product'),
                            'route'   => [
                                'name'       => $request->route()->getName().'.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                    ]
                ],
                'data'        => ProductResource::collection($products),


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
                        'label' => __('products'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'shops.show.products.index' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            'shops.products.index' =>
            array_merge(
                IndexShops::make()->getBreadcrumbs(),
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
