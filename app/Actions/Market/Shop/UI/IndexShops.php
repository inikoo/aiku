<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:33 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Shop\UI;

use App\Actions\OrgAction;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Market\ProductCategory\UI\IndexDepartments;
use App\Actions\Market\ProductCategory\UI\IndexFamilies;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Enums\UI\ShopsTabsEnum;
use App\Http\Resources\Market\DepartmentResource;
use App\Http\Resources\Market\FamilyResource;
use App\Http\Resources\Market\ProductResource;
use App\Http\Resources\Market\ShopResource;
use App\InertiaTable\InertiaTable;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexShops extends OrgAction
{
    private Organisation|Group $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('shops');
        return $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(ShopsTabsEnum::values());

        return $this->handle();
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($prefix = null): LengthAwarePaginator
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

        $queryBuilder = QueryBuilder::for(Shop::class);

        if(class_basename($this->parent) == 'Organisation') {
            $queryBuilder->where('organisation_id', $this->parent->id);
        } else {
            $queryBuilder->where('group_id', $this->parent->id);
        }

        return $queryBuilder
            ->defaultSort('shops.code')
            ->select(['code', 'id', 'name', 'slug', 'type'])
            ->allowedSorts(['code', 'name', 'type'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Group $parent, $prefix): Closure
    {
        return function (InertiaTable $table) use ($prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations()
                ->withEmptyState(
                    class_basename($parent) == 'Organisation' ?
                        [
                            'title'       => __('No shops found'),
                            'description' => $this->canEdit ? __('Get started by creating a shop. ✨') : null,
                            'count'       => $parent->marketStats->number_shops,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new shop'),
                                'label'   => __('shop'),
                                'route'   => [
                                    'name'       => 'grp.org.shops.create',
                                    'parameters' => $parent->slug
                                ]
                            ] : null
                        ] : null
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(): AnonymousResourceCollection
    {
        return ShopResource::collection($this->handle());
    }

    public function htmlResponse(LengthAwarePaginator $shops, ActionRequest $request): Response
    {
        return Inertia::render(
            'Market/Shops',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                'title'       => __('shops'),
                'pageHead'    => [
                    'title'   => __('shops'),
                    'icon'    => [
                        'icon'  => ['fal', 'fa-store-alt'],
                        'title' => __('shop')
                    ],
                    'actions' => [
                        $this->canEdit ? [
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
                    fn () => DepartmentResource::collection(IndexDepartments::run($this->parent, ShopsTabsEnum::DEPARTMENTS->value))
                    : Inertia::lazy(fn () => DepartmentResource::collection(IndexDepartments::run($this->parent, ShopsTabsEnum::DEPARTMENTS->value))),

                ShopsTabsEnum::FAMILIES->value => $this->tab == ShopsTabsEnum::FAMILIES->value ?
                    fn () => FamilyResource::collection(IndexFamilies::run($this->parent))
                    : Inertia::lazy(fn () => FamilyResource::collection(IndexFamilies::run($this->parent))),

                ShopsTabsEnum::PRODUCTS->value => $this->tab == ShopsTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($this->parent))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($this->parent))),


            ]
        )->table($this->tableStructure(parent: $this->parent, prefix: 'shops'))
            ->table(
                IndexDepartments::make()->tableStructure(
                    parent: $this->parent,
                    modelOperations: [],
                    prefix: 'departments'
                )
            )
            ->table(IndexFamilies::make()->tableStructure(parent: $this->parent, prefix: 'families'))
            ->table(IndexDepartments::make()->tableStructure(parent: $this->parent, prefix: 'products'));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        if ($routeName == 'grp.org.shops.index') {
            return
                array_merge(
                    (new ShowDashboard())->getBreadcrumbs(),
                    [
                        [
                            'type'   => 'simple',
                            'simple' => [
                                'route' => [
                                    'name'       => 'grp.org.shops.index',
                                    'parameters' => $routeParameters
                                ],
                                'label' => __('shops'),
                                'icon'  => 'fal fa-bars'
                            ],
                            'suffix' => $suffix

                        ]
                    ]
                );
        }

        return [];
    }
}
