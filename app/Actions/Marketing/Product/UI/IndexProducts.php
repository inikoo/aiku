<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Product\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Marketing\ProductResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexProducts extends InertiaAction
{
    use HasUIProducts;

    private Shop|Tenant|Department $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('products.name', '~*', "\y$value\y")
                    ->orWhere('products.code', '=', $value);
            });
        });


        return QueryBuilder::for(Product::class)
            ->defaultSort('products.code')
            ->select(['products.code', 'products.name', 'products.state', 'products.created_at', 'products.updated_at', 'products.slug', 'shops.slug as shop_slug'])
            ->leftJoin('product_stats', 'products.id', 'product_stats.product_id')
            ->leftJoin('shops', 'products.shop_id', 'shops.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Shop') {
                    $query->where('products.shop_id', $this->parent->id);
                } elseif (class_basename($this->parent) == 'Shop') {
                    $query->where('families.department_id', $this->parent->id);
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
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


    public function jsonResponse(): AnonymousResourceCollection
    {
        return ProductResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $products)
    {
        return Inertia::render(
            'Marketing/Products',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('products'),
                'pageHead'    => [
                    'title'   => __('products'),
                    'create'  => $this->canEdit && $this->routeName=='shops.show.products.index' ? [
                        'route' => [
                            'name'       => 'shops.show.products.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('product')
                    ] : false,
                ],
                'products' => ProductResource::collection($products),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('code');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent    = app('currentTenant');
        $this->initialisation($request);
        return $this->handle();
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisation($request);
        return $this->handle();
    }

    public function inShopInDepartment(Shop $shop, Department $department, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $department;
        $this->initialisation($request);
        return $this->handle();
    }
}
