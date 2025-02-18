<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:37:19 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\UI;

use App\Actions\Catalogue\Collection\UI\ShowCollection;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\Catalogue\WithCollectionSubNavigation;
use App\Actions\Catalogue\WithDepartmentSubNavigation;
use App\Actions\Catalogue\WithSubDepartmentSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\UI\Catalogue\ProductCategoryTabsEnum;
use App\Http\Resources\Catalogue\FamiliesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexFamilies extends OrgAction
{
    use HasCatalogueAuthorisation;
    use WithDepartmentSubNavigation;
    use WithCollectionSubNavigation;
    use WithSubDepartmentSubNavigation;

    private bool $sales = true;

    private Group|Shop|ProductCategory|Organisation|Collection $parent;

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->sales = false;
        $this->initialisationFromGroup(group(), $request)->withTab(ProductCategoryTabsEnum::values());

        return $this->handle($this->parent);
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(ProductCategoryTabsEnum::values());

        return $this->handle(parent: $organisation);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $department;
        $this->initialisationFromShop($shop, $request)->withTab(ProductCategoryTabsEnum::values());

        return $this->handle(parent: $department);
    }

    public function inSubDepartmentInDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, ProductCategory $subDepartment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $subDepartment;
        $this->initialisationFromShop($shop, $request)->withTab(ProductCategoryTabsEnum::values());

        return $this->handle(parent: $subDepartment);
    }

    public function inCollection(Organisation $organisation, Shop $shop, Collection $collection, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $collection;
        $this->initialisationFromShop($shop, $request)->withTab(ProductCategoryTabsEnum::values());

        return $this->handle(parent: $collection);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(ProductCategoryTabsEnum::values());

        return $this->handle(parent: $shop);
    }

    public function handle(Group|Shop|ProductCategory|Organisation|Collection $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('product_categories.name', $value)
                    ->orWhereStartWith('product_categories.code', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(ProductCategory::class);

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }


        $queryBuilder->leftJoin('shops', 'product_categories.shop_id', 'shops.id');
        $queryBuilder->leftJoin('organisations', 'product_categories.organisation_id', '=', 'organisations.id');
        $queryBuilder->leftJoin('product_category_sales_intervals', 'product_category_sales_intervals.product_category_id', 'product_categories.id');
        $queryBuilder->leftJoin('product_category_ordering_intervals', 'product_category_ordering_intervals.product_category_id', 'product_categories.id');
        if ($parent instanceof Group) {
            $queryBuilder->where('product_categories.group_id', $parent->id);
        } elseif (class_basename($parent) == 'Shop') {
            $queryBuilder->where('product_categories.shop_id', $parent->id);
        } elseif (class_basename($parent) == 'Organisation') {
            $queryBuilder->where('product_categories.organisation_id', $parent->id);
        } elseif (class_basename($parent) == 'ProductCategory') {
            if ($parent->type == ProductCategoryTypeEnum::DEPARTMENT) {
                $queryBuilder->where('product_categories.department_id', $parent->id);
            } elseif ($parent->type == ProductCategoryTypeEnum::SUB_DEPARTMENT) {
                $queryBuilder->where('product_categories.sub_department_id', $parent->id);
            } else {
                // todo
                abort(419);
            }
        } elseif (class_basename($parent) == 'Collection') {
            $queryBuilder->join('model_has_collections', function ($join) use ($parent) {
                $join->on('product_categories.id', '=', 'model_has_collections.model_id')
                        ->where('model_has_collections.model_type', '=', 'ProductCategory')
                        ->where('model_has_collections.collection_id', '=', $parent->id);
            });
        }


        return $queryBuilder
            ->defaultSort('product_categories.code')
            ->select([
                'product_categories.id',
                'product_categories.slug',
                'product_categories.code',
                'product_categories.name',
                'product_categories.state',
                'product_categories.description',
                'product_categories.created_at',
                'product_categories.updated_at',
                'departments.slug as department_slug',
                'departments.code as department_code',
                'departments.name as department_name',
                'product_category_stats.number_current_products',
                'shops.slug as shop_slug',
                'shops.code as shop_code',
                'shops.name as shop_name',
                'organisations.name as organisation_name',
                'organisations.slug as organisation_slug',
                'product_category_sales_intervals.sales_grp_currency_all as sales_all',
                'product_category_ordering_intervals.invoices_all as invoices_all',

            ])
            ->leftJoin('product_category_stats', 'product_categories.id', 'product_category_stats.product_category_id')
            ->where('product_categories.type', ProductCategoryTypeEnum::FAMILY)
            ->leftjoin('product_categories as departments', 'departments.id', 'product_categories.department_id')
            ->allowedSorts(['code', 'name', 'shop_code', 'department_code','number_current_products'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(Group|Shop|ProductCategory|Organisation|Collection $parent, ?array $modelOperations = null, $prefix = null, $canEdit = false, $sales = true): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit, $sales) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }

            $table
                ->defaultSort('code')
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No families found"),
                            'description' => $canEdit ?
                                $parent->catalogueStats->number_shops == 0 ? __("In fact, is no even a shop yet 🤷🏽‍♂️") : ''
                                : '',
                            'count'       => $parent->catalogueStats->number_families,
                            'action'      => $canEdit && $parent->catalogueStats->number_shops == 0
                                ?
                                [
                                    'type'    => 'button',
                                    'style'   => 'create',
                                    'tooltip' => __('new shop'),
                                    'label'   => __('shop'),
                                    'route'   => [
                                        'name'       => 'grp.org.shops.show.catalogue.families.create',
                                        'parameters' => [$parent->slug]
                                    ]
                                ] : null

                        ],
                        'Shop', 'ProductCategory' => [
                            'title' => __("No families found"),
                            'count' => $parent->stats->number_families,
                        ],
                        default => null
                    }
                )
                ->withGlobalSearch()
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon')
                ->withModelOperations($modelOperations);

            if ($sales) {
                $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                        ->column(key: 'sales', label: __('sales'), canBeHidden: false, sortable: true, searchable: true)
                        ->column(key: 'invoices', label: __('invoices'), canBeHidden: false, sortable: true, searchable: true);
            } else {
                if ($parent instanceof Organisation) {
                    $table->column(key: 'shop_code', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
                    $table->column(key: 'department_code', label: __('department'), canBeHidden: false, sortable: true, searchable: true);
                }
                $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);

                if ($parent instanceof Group) {
                    $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, sortable: true, searchable: true)
                    ->column(key: 'shop_name', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
                }

                if (class_basename($parent) != 'Collection') {
                    $table->column(key: 'number_current_products', label: __('current products'), canBeHidden: false, sortable: true, searchable: true);
                }

                if (class_basename($parent) == 'Collection') {
                    $table->column(key: 'actions', label: __('action'), canBeHidden: false, sortable: true, searchable: true);
                }
            }
        };
    }

    public function jsonResponse(LengthAwarePaginator $families): AnonymousResourceCollection
    {
        return FamiliesResource::collection($families);
    }

    public function htmlResponse(LengthAwarePaginator $families, ActionRequest $request): Response
    {
        $navigation = ProductCategoryTabsEnum::navigation();
        if ($this->parent instanceof Group) {
            unset($navigation[ProductCategoryTabsEnum::SALES->value]);
        }
        $subNavigation = null;
        if ($this->parent instanceof ProductCategory) {
            if ($this->parent->type == ProductCategoryTypeEnum::DEPARTMENT) {
                $subNavigation = $this->getDepartmentSubNavigation($this->parent);
            } elseif ($this->parent->type == ProductCategoryTypeEnum::SUB_DEPARTMENT) {
                $subNavigation = $this->getSubDepartmentSubNavigation($this->parent);
            }
        }
        if ($this->parent instanceof Collection) {
            $subNavigation = $this->getCollectionSubNavigation($this->parent);
        }


        $title = __('families');
        $model = '';
        $icon  = [
            'icon'  => ['fal', 'fa-folder'],
            'title' => __('family')
        ];
        $afterTitle = null;
        $iconRight = null;

        if ($this->parent instanceof ProductCategory) {
            if ($this->parent->type == ProductCategoryTypeEnum::DEPARTMENT) {
                $title = $this->parent->name;
                $model = '';
                $icon  = [
                    'icon'  => ['fal', 'fa-folder-tree'],
                    'title' => __('department')
                ];
                $iconRight    = [
                    'icon' => 'fal fa-folder',
                ];
                $afterTitle = [

                    'label'     => __('Families')
                ];

                $createRoute = "grp.org.shops.show.catalogue.departments.show.families.create";

            } elseif ($this->parent->type == ProductCategoryTypeEnum::SUB_DEPARTMENT) {
                $title = $this->parent->name;
                $model = '';
                $icon  = [
                    'icon'  => ['fal', 'fa-dot-circle'],
                    'title' => __('sub department')
                ];
                $iconRight    = [
                    'icon' => 'fal fa-folder',
                ];
                $afterTitle = [

                    'label'     => __('Families')
                ];

                $createRoute = "grp.org.shops.show.catalogue.departments.show.sub-departments.show.family.create";

            }
        } elseif ($this->parent instanceof Collection) {
            $title = $this->parent->name;
            $model = __('collection');
            $icon  = [
                'icon'  => ['fal', 'fa-cube'],
                'title' => __('collection')
            ];
            $iconRight    = [
                'icon' => 'fal fa-folder',
            ];
            $afterTitle = [
                'label'     => __('Families')
            ];
        }

        $routes = null;
        if ($this->parent instanceof Collection) {
            $routes = [
                        'dataList'  => [
                            'name'          => 'grp.json.shop.catalogue.families',
                            'parameters'    => [
                                'shop'  => $this->parent->shop->slug,
                                'scope' => $this->parent->slug
                            ]
                        ],
                        'submitAttach'  => [
                            'name'          => 'grp.models.collection.attach-models',
                            'parameters'    => [
                                'collection' => $this->parent->id
                            ]
                        ],
                        'detach'        => [
                            'name'          => 'grp.models.collection.detach-models',
                            'parameters'    => [
                                'collection' => $this->parent->id
                            ]
                        ]
                    ];
        }

        return Inertia::render(
            'Org/Catalogue/Families',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('families'),
                'pageHead'    => [
                    'title'         => $title,
                    'icon'          => $icon,
                    'model'         => $model,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'actions'       => [
                        $this->canEdit ? (
                            class_basename($this->parent) == 'ProductCategory' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new family'),
                            'label'   => __('family'),
                            'route'   => [
                                'name'       => $createRoute,
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false
                        ) : false,
                        class_basename($this->parent) == 'Collection' ? [
                            'type'     => 'button',
                            'style'    => 'secondary',
                            'key'      => 'attachFamily',
                            'icon'     => 'fal fa-plus',
                            'tooltip'  => __('Attach family to this collection'),
                            'label'    => __('Attach family'),
                        ] : false
                    ],
                    'subNavigation' => $subNavigation,
                ],
                'routes'      => $routes,
                'data'        => FamiliesResource::collection($families),
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => $navigation,
                ],
                ProductCategoryTabsEnum::INDEX->value => $this->tab == ProductCategoryTabsEnum::INDEX->value ?
                fn () => FamiliesResource::collection($families)
                : Inertia::lazy(fn () => FamiliesResource::collection($families)),

                ProductCategoryTabsEnum::SALES->value => $this->tab == ProductCategoryTabsEnum::SALES->value ?
                fn () => FamiliesResource::collection($families)
                : Inertia::lazy(fn () => FamiliesResource::collection($families)),
            ]
        )->table($this->tableStructure(parent: $this->parent, modelOperations:null, canEdit:false, prefix:ProductCategoryTabsEnum::INDEX->value, sales: false))
        ->table($this->tableStructure(parent: $this->parent, modelOperations:null, canEdit:false, prefix:ProductCategoryTabsEnum::SALES->value, sales: $this->sales));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Families'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.catalogue.families.index' => array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.departments.show.families.index' => array_merge(
                ShowDepartment::make()->getBreadcrumbs('grp.org.shops.show.catalogue.departments.show', $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.catalogue.departments.show.families.index',
                        'parameters' => [
                            $routeParameters['organisation'],
                            $routeParameters['shop'],
                            $routeParameters['department']
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.departments.show.sub-departments.show.family.index' => array_merge(
                ShowSubDepartment::make()->getBreadcrumbs('grp.org.shops.show.catalogue.departments.show.sub-departments.show', $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.catalogue.departments.show.sub-departments.show.family.index',
                        'parameters' => [
                            $routeParameters['organisation'],
                            $routeParameters['shop'],
                            $routeParameters['department'],
                            $routeParameters['subDepartment']
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.collections.families.index' =>
            array_merge(
                ShowCollection::make()->getBreadcrumbs('grp.org.shops.show.catalogue.collections.show', $routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            'grp.overview.catalogue.families.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs($routeParameters),
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

    protected function getElementGroups($parent): array
    {
        return
            [
                'state' => [
                    'label'    => __('State'),
                    'elements' => array_merge_recursive(
                        ProductCategoryStateEnum::labels(),
                        ProductCategoryStateEnum::countFamily($parent)
                    ),
                    'engine'   => function ($query, $elements) {
                        $query->whereIn('product_categories.state', $elements);
                    }
                ]
            ];
    }
}
