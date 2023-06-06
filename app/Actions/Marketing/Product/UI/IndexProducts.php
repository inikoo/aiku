<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Product\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Catalogue\CatalogueHub;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Marketing\ProductResource;
use App\Models\Marketing\ProductCategory;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexProducts extends InertiaAction
{
    private Shop|Tenant $parent;
    public function handle($parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('products.name', '~*', "\y$value\y")
                    ->orWhere('products.code', '=', $value);
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::PRODUCTS->value);
        return QueryBuilder::for(Product::class)
            ->defaultSort('products.code')
            ->select([
                'products.code',
                'products.name',
                'products.state',
                'products.created_at',
                'products.updated_at',
                'products.slug',
                'shops.slug as shop_slug'])
            ->leftJoin('product_stats', 'products.id', 'product_stats.product_id')
            ->leftJoin('shops', 'products.shop_id', 'shops.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('products.shop_id', $parent->id);
                } elseif (class_basename($parent) == 'Shop') {
                    $query->where('families.department_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::PRODUCTS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations) {
            $table
                ->name(TabsAbbreviationEnum::PRODUCTS->value)
                ->pageName(TabsAbbreviationEnum::PRODUCTS->value.'Page')
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.products.edit');
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.products.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $products): AnonymousResourceCollection
    {
        return ProductResource::collection($products);
    }


    public function htmlResponse(LengthAwarePaginator $products, ActionRequest $request): Response
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Marketing/Products',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('Products'),
                'pageHead'    => [
                    'title'  => __('products'),
                    'create' => $this->canEdit && $this->routeName == 'shops.show.catalogue.hub.products.index' ? [
                        'route' => [
                            'name'       => 'shops.show.catalogue.hub.products.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('products')
                    ] : false,
                ],
                'data'        => ProductResource::collection($products),


            ]
        )->table($this->tableStructure($parent));
    }


    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($shop);
    }

    public function inDepartmentInShop(Shop $shop, ProductCategory $department, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($department);
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
            'shops.show.catalogue.hub.products.index' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('shops.show.catalogue.hub', $routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            'catalogue.hub.products.index' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('catalogue.hub', []),
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
