<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 11:47:26 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Actions\Catalogue\Collection\UI\ShowCollection;
use App\Actions\Catalogue\ProductCategory\UI\ShowDepartment;
use App\Actions\Catalogue\ProductCategory\UI\ShowFamily;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\Catalogue\WithCollectionSubNavigation;
use App\Actions\Catalogue\WithDepartmentSubNavigation;
use App\Actions\Catalogue\WithFamilySubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Http\Resources\Catalogue\ProductsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexProducts extends OrgAction
{
    use HasCatalogueAuthorisation;
    use WithDepartmentSubNavigation;
    use WithFamilySubNavigation;
    use WithCollectionSubNavigation;

    private string $bucket;

    private Shop|ProductCategory|Organisation|Collection $parent;

    protected function getElementGroups(Shop|ProductCategory|Organisation|Collection|ShopifyUser $parent, $bucket = null): array
    {
        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    ProductStateEnum::labels($bucket),
                    ProductStateEnum::count($parent, $bucket)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }

    public function handle(Shop|ProductCategory|Organisation|Collection|ShopifyUser $parent, $prefix = null, $bucket = null): LengthAwarePaginator
    {
        if ($bucket) {
            $this->bucket = $bucket;
        }

        $addSelects = [];

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('products.name', $value)
                    ->orWhereStartWith('products.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Product::class);
        $queryBuilder->where('products.is_main', true);
        if (class_basename($parent) == 'Shop') {
            $queryBuilder->where('products.shop_id', $parent->id);
            if ($bucket == 'current') {
                $queryBuilder->whereIn('products.state', [ProductStateEnum::ACTIVE, ProductStateEnum::DISCONTINUING]);
                foreach ($this->getElementGroups($parent, $bucket) as $key => $elementGroup) {
                    $queryBuilder->whereElementGroup(
                        key: $key,
                        allowedElements: array_keys($elementGroup['elements']),
                        engine: $elementGroup['engine'],
                        prefix: $prefix
                    );
                }
            } elseif ($bucket == 'discontinued') {
                $queryBuilder->where('products.state', ProductStateEnum::DISCONTINUED);
            } elseif ($bucket == 'in_process') {
                $queryBuilder->where('products.state', ProductStateEnum::IN_PROCESS);
            } else {
                foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                    $queryBuilder->whereElementGroup(
                        key: $key,
                        allowedElements: array_keys($elementGroup['elements']),
                        engine: $elementGroup['engine'],
                        prefix: $prefix
                    );
                }
            }
        } elseif (class_basename($parent) == 'Organisation') {
            $queryBuilder->where('products.organisation_id', $parent->id);
            $queryBuilder->leftJoin('shops', 'products.shop_id', 'shops.id');
            $queryBuilder->addSelect(
                'shops.slug as shop_slug',
                'shops.code as shop_code',
                'shops.name as shop_name',
            );
        } elseif (class_basename($parent) == 'ProductCategory') {
            if ($parent->type == ProductCategoryTypeEnum::DEPARTMENT) {
                $queryBuilder->where('products.department_id', $parent->id);
            } elseif ($parent->type == ProductCategoryTypeEnum::FAMILY) {
                $queryBuilder->where('products.family_id', $parent->id);
            } else {
                abort(419);
            }
        } elseif (class_basename($parent) == 'Collection') {
            $queryBuilder->join('model_has_collections', function ($join) use ($parent) {
                $join->on('products.id', '=', 'model_has_collections.model_id')
                    ->where('model_has_collections.model_type', '=', 'Product')
                    ->where('model_has_collections.collection_id', '=', $parent->id);
            });
        } elseif ($parent instanceof ShopifyUser) {
            $queryBuilder->join('shopify_user_has_products', function ($join) use ($parent) {
                $join->on('products.id', '=', 'shopify_user_has_products.product_id')
                    ->where('shopify_user_has_products.shopify_user_id', '=', $parent->id);
            });

            $addSelects = [
                'shopify_user_has_products.id as portfolio_id',
                'shopify_user_has_products.shopify_product_id',
                'shopify_user_has_products.shopify_user_id'
            ];
        } else {
            abort(419);
        }

        if (class_basename($parent) != 'Shop' && class_basename($parent) != 'ShopifyUser') {
            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }

        $queryBuilder
            ->defaultSort('products.code')
            ->select([
                'products.id',
                'products.code',
                'products.name',
                'products.state',
                'products.created_at',
                'products.updated_at',
                'products.slug',
                ...$addSelects
            ])
            ->leftJoin('product_stats', 'products.id', 'product_stats.product_id');

        return $queryBuilder->allowedSorts(['code', 'name', 'shop_slug', 'department_slug', 'family_slug'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Shop|ProductCategory|Organisation|Collection|ShopifyUser $parent, ?array $modelOperations = null, $prefix = null, $canEdit = false, string $bucket = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit, $bucket) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            if (class_basename($parent) == 'Shop') {
                if ($bucket == 'current' or $bucket == 'all') {
                    foreach ($this->getElementGroups($parent, $bucket) as $key => $elementGroup) {
                        $table->elementGroup(
                            key: $key,
                            label: $elementGroup['label'],
                            elements: $elementGroup['elements']
                        );
                    }
                }
            } elseif (class_basename($parent) != 'ShopifyUser') {
                foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No products found"),
                            'description' => $canEdit && $parent->catalogueStats->number_shops == 0 ? __(
                                'Get started by creating a new shop. ✨'
                            ) : '',
                            'count'       => $parent->catalogueStats->number_products,
                            'action'      => $canEdit && $parent->catalogueStats->number_shops == 0 ? [
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
                            'title' => match ($bucket) {
                                'in_process' => __("There is no products in process"),
                                'discontinued' => __('There is no discontinued products'),
                                default => __("No products found"),
                            },


                            'count' => match ($bucket) {
                                'current' => $parent->stats->number_current_products,
                                'in_process' => $parent->stats->number_products_state_in_process,
                                'discontinued' => $parent->stats->number_products_state_discontinued,
                                default => $parent->stats->number_products,
                            }

                        ],
                        'ProductCategory' => [
                            'title' => $this->parent->type == ProductCategoryTypeEnum::DEPARTMENT ? __("There is no families in this department") : __("There is no products in this family"),
                            'count' => $this->parent->stats->number_products
                        ],
                        default => null
                    }
                );
            if (!($parent instanceof Shop and in_array($bucket, ['in_process', 'discontinued']))) {
                $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');
            }
            if ($parent instanceof Organisation) {
                $table->column(
                    key: 'shop_code',
                    label: __('shop'),
                    canBeHidden: false,
                    sortable: true,
                    searchable: true
                );
            }
            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);

            if ($parent instanceof Collection or $parent instanceof ShopifyUser) {
                $table->column(key: 'actions', label: __('action'), canBeHidden: false, sortable: true, searchable: true);
            }
        };
    }

    public function jsonResponse(LengthAwarePaginator $products): AnonymousResourceCollection
    {
        return ProductsResource::collection($products);
    }

    public function getShopProductsSubNavigation(): array
    {
        $stats = $this->parent->stats;

        return [

            [
                'label'  => __('Current'),
                'root'   => 'grp.org.shops.show.catalogue.products.current_products.',
                'href'   => [
                    'name'       => 'grp.org.shops.show.catalogue.products.current_products.index',
                    'parameters' => [
                        $this->organisation->slug,
                        $this->shop->slug
                    ]
                ],
                'number' => $stats->number_current_products
            ],

            [
                'label'  => __('In Process'),
                'root'   => 'grp.org.shops.show.catalogue.products.in_process_products.',
                'href'   => [
                    'name'       => 'grp.org.shops.show.catalogue.products.in_process_products.index',
                    'parameters' => [
                        $this->organisation->slug,
                        $this->shop->slug
                    ]
                ],
                'number' => $stats->number_products_state_in_process
            ],
            [
                'label'  => __('Discontinued'),
                'root'   => 'grp.org.shops.show.catalogue.products.discontinued_products.',
                'href'   => [
                    'name'       => 'grp.org.shops.show.catalogue.products.discontinued_products.index',
                    'parameters' => [
                        $this->organisation->slug,
                        $this->shop->slug
                    ]
                ],
                'number' => $stats->number_products_state_discontinued,
                'align'  => 'right'
            ],
            [
                'label'  => __('All'),
                'icon'   => 'fal fa-bars',
                'root'   => 'grp.org.shops.show.catalogue.products.all_products.',
                'href'   => [
                    'name'       => 'grp.org.shops.show.catalogue.products.all_products.index',
                    'parameters' => [
                        $this->organisation->slug,
                        $this->shop->slug
                    ]
                ],
                'number' => $stats->number_products,
                'align'  => 'right'
            ],

        ];
    }

    public function htmlResponse(LengthAwarePaginator $products, ActionRequest $request): Response
    {
        $subNavigation = null;
        if ($this->parent instanceof ProductCategory) {
            if ($this->parent->type == ProductCategoryTypeEnum::DEPARTMENT) {
                $subNavigation = $this->getDepartmentSubNavigation($this->parent);
            } elseif ($this->parent->type == ProductCategoryTypeEnum::FAMILY) {
                $subNavigation = $this->getFamilySubNavigation($this->parent, $this->parent, $request);
            }
        } elseif ($this->parent instanceof Collection) {
            $subNavigation = $this->getCollectionSubNavigation($this->parent);
        } elseif ($this->parent instanceof Shop) {
            $subNavigation = $this->getShopProductsSubNavigation();
        }


        $title      = __('products');
        $icon       = [
            'icon'  => ['fal', 'fa-cube'],
            'title' => __('product')
        ];
        $afterTitle = null;
        $iconRight  = null;
        $model      = null;

        if ($this->parent instanceof ProductCategory) {
            if ($this->parent->type == ProductCategoryTypeEnum::DEPARTMENT) {
                $title      = $this->parent->name;
                $model      = '';
                $icon       = [
                    'icon'  => ['fal', 'fa-folder-tree'],
                    'title' => __('Department')
                ];
                $iconRight  = [
                    'icon' => 'fal fa-cube',
                ];
                $afterTitle = [
                    'label' => __('Products')
                ];
            } elseif ($this->parent->type == ProductCategoryTypeEnum::FAMILY) {
                $title      = $this->parent->name;
                $model      = '';
                $icon       = [
                    'icon'  => ['fal', 'fa-folder'],
                    'title' => __('Family')
                ];
                $iconRight  = [
                    'icon' => 'fal fa-cube',
                ];
                $afterTitle = [
                    'label' => __('Products')
                ];
            }
        } elseif ($this->parent instanceof Collection) {
            $title      = $this->parent->name;
            $model      = __('collection');
            $icon       = [
                'icon'  => ['fal', 'fa-cube'],
                'title' => __('collection')
            ];
            $iconRight  = [
                'icon' => 'fal fa-cube',
            ];
            $afterTitle = [
                'label' => __('Products')
            ];
        } elseif ($this->parent instanceof Shop) {
            $model = '';
        }
        $routes = null;
        if ($this->parent instanceof Collection) {
            $routes = [
                'dataList'     => [
                    'name'       => 'grp.json.shop.catalogue.collection.products',
                    'parameters' => [
                        'shop'  => $this->parent->shop->slug,
                        'scope' => $this->parent->slug
                    ]
                ],
                'submitAttach' => [
                    'name'       => 'grp.models.collection.attach-models',
                    'parameters' => [
                        'collection' => $this->parent->id
                    ]
                ],
                'detach'       => [
                    'name'       => 'grp.models.collection.detach-models',
                    'parameters' => [
                        'collection' => $this->parent->id
                    ]
                ]
            ];
        }

        return Inertia::render(
            'Org/Catalogue/Products',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $this->parent,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Products'),
                'pageHead'    => [
                    'title'         => $title,
                    'model'         => $model,
                    'icon'          => $icon,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'actions'       => [
                        $this->canEdit
                        && class_basename(
                            $this->parent
                        ) == 'ProductCategory'
                        && $this->parent->type == ProductCategoryTypeEnum::FAMILY ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new product'),
                            'label'   => __('product'),
                            'route'   => [
                                'name'       => str_replace('index', 'create', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,

                        class_basename($this->parent) == 'Collection' ? [
                            'type'    => 'button',
                            'style'   => 'secondary',
                            'key'     => 'attach-product',
                            'icon'    => 'fal fa-plus',
                            'tooltip' => __('Attach product to this collection'),
                            'label'   => __('Attach product'),
                        ] : false
                    ],
                    'subNavigation' => $subNavigation,
                ],
                'routes'      => $routes,
                'data'        => ProductsResource::collection($products),


            ]
        )->table($this->tableStructure(parent: $this->parent, bucket: $this->bucket));
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle(parent: $organisation, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFamily(Organisation $organisation, Shop $shop, ProductCategory $family, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->parent = $family;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $family, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFamilyInDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, ProductCategory $family, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->parent = $family;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $family, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->parent = $department;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $department, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inCollection(Organisation $organisation, Shop $shop, Collection $collection, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->parent = $collection;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $collection, bucket: $this->bucket);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $shop, bucket: $this->bucket);
    }

    public function inRetina(ActionRequest $request): LengthAwarePaginator
    {
        $this->asAction = true;
        $this->bucket = 'all';
        $shop = $request->get('website')->shop;
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $shop, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function current(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'current';
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $shop, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inProcess(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'in_process';
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $shop, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function discontinued(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'discontinued';
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle(parent: $shop, bucket: $this->bucket);
    }

    public function getBreadcrumbs(Shop|ProductCategory|Organisation|Collection $parent, string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Products'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };


        return match ($routeName) {
            'grp.org.shops.show.catalogue.products.current_products.index', =>
            array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    trim('('.__('Current').') '.$suffix)
                )
            ),
            'grp.org.shops.show.catalogue.products.in_process_products.index' =>
            array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    trim('('.__('In process').') '.$suffix)
                )
            ),
            'grp.org.shops.show.catalogue.products.discontinued_products.index' =>
            array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    trim('('.__('Discontinued').') '.$suffix)
                )
            ),
            'grp.org.shops.show.catalogue.products.all_products.index' =>
            array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.departments.show.products.index' =>
            array_merge(
                ShowDepartment::make()->getBreadcrumbs(
                    'grp.org.shops.show.catalogue.departments.show',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.departments.show.families.show.products.index' =>
            array_merge(
                ShowFamily::make()->getBreadcrumbs(
                    $parent,
                    'grp.org.shops.show.catalogue.departments.show.families.show',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            'grp.org.shops.show.catalogue.families.show.products.index' =>
            array_merge(
                ShowFamily::make()->getBreadcrumbs(
                    $parent,
                    'grp.org.shops.show.catalogue.families.show',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            'grp.org.shops.show.catalogue.collections.products.index' =>
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


            default => []
        };
    }
}
