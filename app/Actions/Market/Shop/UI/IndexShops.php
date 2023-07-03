<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:33 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Shop\UI;

use App\Actions\InertiaAction;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Market\ProductCategory\UI\IndexDepartments;
use App\Actions\Market\ProductCategory\UI\IndexFamilies;
use App\Actions\UI\Dashboard\Dashboard;
use App\Enums\UI\ShopsTabsEnum;
use App\Http\Resources\Market\DepartmentResource;
use App\Http\Resources\Market\FamilyResource;
use App\Http\Resources\Market\ProductResource;
use App\Http\Resources\Market\ShopResource;
use App\InertiaTable\InertiaTable;
use App\Models\Market\Shop;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property array $breadcrumbs
 * @property bool $canEdit
 * @property string $title
 */
class IndexShops extends InertiaAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('shops.name', $value)
                    ->orWhere('shops.code', 'ilike', "$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Shop::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('shops.code')
            ->select(['code', 'id', 'name', 'slug','type','subtype'])
            ->allowedSorts(['code', 'name','type','subtype'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix): Closure
    {
        return function (InertiaTable $table) use ($prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations()
                ->withEmptyState(
                    [
                        'title'       => __('no shops'),
                        'description' => $this->canEdit ? __('Get started by creating a new shop.') : null,
                        'count'       => app('currentTenant')->stats->number_shops,
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
                    ]
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'subtype', label: __('subtype'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request)->withTab(ShopsTabsEnum::values());
        return $this->handle();
    }

    public function jsonResponse(): AnonymousResourceCollection
    {
        return ShopResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $shops, ActionRequest $request): Response
    {

        $scope=app('currentTenant');

        return Inertia::render(
            'Market/Shops',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('shops'),
                'pageHead'    => [
                    'title'   => __('shops'),
                    'actions' => [
                        $this->canEdit && $this->routeName=='shops.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new shop'),
                            'label'   => __('shop'),
                            'route'   => [
                                'name'       => 'shops.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => ShopsTabsEnum::navigation(),
                ],


                ShopsTabsEnum::SHOPS->value => $this->tab == ShopsTabsEnum::SHOPS->value ?
                    fn () => ShopResource::collection($shops)
                    : Inertia::lazy(fn () => ShopResource::collection($shops)),

                ShopsTabsEnum::DEPARTMENTS->value => $this->tab == ShopsTabsEnum::DEPARTMENTS->value ?
                    fn () => DepartmentResource::collection(IndexDepartments::run($scope, ShopsTabsEnum::DEPARTMENTS->value))
                    : Inertia::lazy(fn () => DepartmentResource::collection(IndexDepartments::run($scope, ShopsTabsEnum::DEPARTMENTS->value))),

                ShopsTabsEnum::FAMILIES->value => $this->tab == ShopsTabsEnum::FAMILIES->value ?
                    fn () => FamilyResource::collection(IndexFamilies::run($scope))
                    : Inertia::lazy(fn () => FamilyResource::collection(IndexFamilies::run($scope))),

                ShopsTabsEnum::PRODUCTS->value => $this->tab == ShopsTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($scope))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($scope))),


            ]
        )->table($this->tableStructure(prefix: 'shops'))
            ->table(
                IndexDepartments::make()->tableStructure(
                    parent:$scope,
                    modelOperations: [],
                    prefix: 'departments'
                )
            )
            ->table(IndexFamilies::make()->tableStructure(parent:$scope, prefix: 'families'))
            ->table(IndexDepartments::make()->tableStructure(parent:$scope, prefix: 'products'));
    }

    public function getBreadcrumbs($suffix=null): array
    {
        return
            array_merge(
                (new Dashboard())->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'shops.index'
                            ],
                            'label' => __('shops'),
                            'icon'  => 'fal fa-bars'
                        ],
                        'suffix'=> $suffix

                    ]
                ]
            );
    }
}
