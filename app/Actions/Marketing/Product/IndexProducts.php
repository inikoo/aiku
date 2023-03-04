<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 21 Febr 2023 17:54:17 Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Product;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Http\Resources\Marketing\FamilyResource;
use App\Http\Resources\Marketing\ProductResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\InertiaTableCustomerResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexProducts extends InertiaAction
{
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
                } elseif(class_basename($this->parent) == 'Shop') {
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
                'title' => __('products'),
                'pageHead' => [
                    'title' => __('products'),
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


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);
        $this->parent = app('currentTenant');
        $this->routeName = $request->route()->getName();

        return $this->handle();
    }

    public function inShop(Shop $shop): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->validateAttributes();

        return $this->handle();
    }

    public function inShopInDepartment(Shop $shop, Department $department): LengthAwarePaginator
    {
        $this->parent = $department;
        $this->validateAttributes();

        return $this->handle();
    }

    public function getBreadcrumbs(string $routeName, Shop|Tenant|Department $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route' => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel' => [
                        'label' => __('products')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'products.index' => $headCrumb(),
            'shops.show.products.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }

}
