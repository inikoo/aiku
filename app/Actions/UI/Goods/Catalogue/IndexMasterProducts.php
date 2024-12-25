<?php

/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-11h-47m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\UI\Goods\Catalogue;

use App\Actions\GrpAction;
use App\Actions\UI\Goods\ShowGoodsDashboard;
use App\Http\Resources\Goods\Catalogue\MasterProductsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Goods\MasterProduct;
use App\Models\Goods\MasterShop;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexMasterProducts extends GrpAction
{
    use WithMasterCatalogueSubnavigation;

    private Group|MasterShop $parent;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("goods.{$this->group->id}.view");
    }

    public function handle(Group|MasterShop $parent, $prefix = null, $bucket = null): LengthAwarePaginator
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('master_products.code', $value)
                    ->orWhereStartWith('master_products.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(MasterProduct::class);
        if ($parent instanceof Group) {
            $queryBuilder->where('master_products.group_id', $parent->id);
        } elseif ($parent instanceof MasterShop) {
            $queryBuilder
            ->join('master_shop_has_master_products', 'master_shop_has_master_products.master_product_id', '=', 'master_products.id')
            ->where('master_shop_has_master_products.master_shop_id', $parent->id);
        }

        return $queryBuilder
            ->defaultSort('master_products.code')
            ->select(
                [
                        'master_shops.id',
                        'master_shops.code',
                        'master_shops.name',
                        'master_shops.slug',
                        'master_shops.state',
                        'master_shops.status',
                        'master_shops.price',
                    ]
            )
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(?array $modelOperations = null, $prefix = null)
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    [
                            'title'       => __("No master shops found"),
                        ],
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $masterProducts): AnonymousResourceCollection
    {
        return MasterProductsResource::collection($masterProducts);
    }

    public function htmlResponse(LengthAwarePaginator $masterProducts, ActionRequest $request): Response
    {
        if ($this->parent instanceof Group) {
            $subNavigation = $this->getMasterCatalogueSubnavigation($this->parent);
            $title = __('master products');
            $model = '';
            $icon  = [
                'icon'  => ['fal', 'fa-cube'],
                'title' => __('master products')
            ];
            $afterTitle = null;
            $iconRight    = null;
        } elseif ($this->parent instanceof MasterShop) {
            $subNavigation = $this->getMasterShopNavigation($this->parent);
            $title = $this->parent->name;
            $model = '';
            $icon  = [
                'icon'  => ['fal', 'fa-store-alt'],
                'title' => __('master shop')
            ];
            $afterTitle = [
                'label'     => __('Products')
            ];
            $iconRight    = [
                'icon' => 'fal fa-cube',
            ];
        }

        return Inertia::render(
            'Goods/MasterShops',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('master products'),
                'pageHead'    => [
                    'title'         => $title,
                    'icon'          => $icon,
                    'model'         => $model,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'subNavigation' => $subNavigation,
                ],
                'data'        => MasterProductsResource::collection($masterProducts),

            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Master products'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        return match ($routeName) {
            'grp.goods.catalogue.products.index' =>
            array_merge(
                ShowGoodsDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => []
                    ],
                    $suffix
                ),
            ),
            'grp.goods.catalogue.shops.show.products.index' =>
            array_merge(
                ShowMasterShop::make()->getBreadcrumbs($routeName, $routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => [
                            'masterShop' => $this->parent->slug
                        ]
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $group = group();
        $this->parent = $group;
        $this->initialisation($group, $request);
        return $this->handle($group, $request);
    }

    public function inMasterShop(MasterShop $masterShop, ActionRequest $request): LengthAwarePaginator
    {
        $group = group();
        $this->parent = $masterShop;
        $this->initialisation($group, $request);
        return $this->handle($masterShop, $request);
    }

}
