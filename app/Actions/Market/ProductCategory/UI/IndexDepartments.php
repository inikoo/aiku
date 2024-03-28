<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:37:19 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ProductCategory\UI;

//use App\Actions\UI\Catalogue\CatalogueHub;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Http\Resources\Market\DepartmentsResource;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexDepartments extends OrgAction
{
    private Shop|ProductCategory|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            $this->canEdit = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");

            return $request->user()->hasPermissionTo("shops.{$this->organisation->id}.view");
        } else {
            $this->canEdit = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");

            return $request->user()->hasPermissionTo("products.{$this->shop->id}.view");
        }
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle(parent: $organisation);
    }

    public function inProductCategory(Organisation $organisation, Shop $shop, ProductCategory $productCategory, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $productCategory;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $productCategory);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $shop);
    }


    public function handle(Shop|ProductCategory|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('product_categories.name', $value)
                    ->orWhere('product_categories.slug', 'ilike', "$value%");
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(ProductCategory::class);

        /*
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }
        */

        return $queryBuilder
            ->defaultSort('product_categories.code')
            ->select([
                'product_categories.slug',
                'product_categories.code',
                'product_categories.name',
                'product_categories.state',
                'product_categories.description',
                'product_categories.created_at',
                'product_categories.updated_at',
            ])
            ->leftJoin('product_category_stats', 'product_categories.id', 'product_category_stats.product_category_id')
            ->where('is_family', false)
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('product_categories.parent_type', 'Shop');
                    $query->where('product_categories.parent_id', $parent->id);
                } elseif (class_basename($parent) == 'Organisation') {
                    $query->leftJoin('shops', 'product_categories.shop_id', 'shops.id');
                    $query->addSelect('shops.slug as shop_slug');
                }
            })
            ->allowedSorts(['code', 'name'])
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
                ->defaultSort('slug')
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No departments found"),
                            'description' => $this->canEdit && $parent->marketStats->number_shops == 0 ? __('Get started by creating a shop. ✨')
                                : __("In fact, is no even a shop yet 🤷🏽‍♂️"),
                            'count'       => $parent->marketStats->number_departments,
                            'action'      => $this->canEdit && $parent->marketStats->number_shops == 0 ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new shop'),
                                'label'   => __('shop'),
                                'route'   => [
                                    'name'       => 'grp.org.shops.create',
                                    'parameters' => [$parent->slug]
                                ]
                            ] : null
                        ],
                        'Shop' => [
                            'title'       => __("No departments found"),
                            'description' => $this->canEdit ? __('Get started by creating a new department. ✨')
                                : null,
                            'count'       => $parent->stats->number_departments,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new department'),
                                'label'   => __('department'),
                                'route'   => [
                                    'name'       => 'grp.org.shops.show.departments.create',
                                    'parameters' => [$parent->organisation->slug,$parent->slug]
                                ]
                            ] : null
                        ],
                        default => null
                    }
                    /*
                    [
                        'title'       => __('no departments'),
                        'description' => $this->canEdit ? __('Get started by creating a new department.') : null,
                        'count'       => app('currentTenant')->stats->number_shops,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new department'),
                            'label'   => __('department'),
                            'route'   => [
                                'name'       => 'shops.departments.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : null
                    ]
                    */
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $departments): AnonymousResourceCollection
    {
        return DepartmentsResource::collection($departments);
    }

    public function htmlResponse(LengthAwarePaginator $departments, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $container = null;
        if (class_basename($scope) == 'Shop') {
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'Market/Departments',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Departments'),
                'pageHead'    => [
                    'title'     => __('departments'),
                    'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-folder-tree'],
                        'title' => __('department')
                    ],
                    'actions'   => [
                        $this->canEdit && $request->route()->getName() == 'shops.show.departments.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new department'),
                            'label'   => __('department'),
                            'route'   => [
                                'name'       => 'shops.show.departments.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                    ]
                ],
                'data'        => DepartmentsResource::collection($departments),
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
                        'label' => __('departments'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.catalogue.departments.index' =>
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


            default => []
        };
    }
}
