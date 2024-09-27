<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:38 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Shop\UI;

use App\Actions\OrgAction;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Catalogue\DepartmentResource;
use App\Http\Resources\Catalogue\FamilyResource;
use App\Http\Resources\Catalogue\ProductResource;
use App\Http\Resources\Catalogue\ShopResource;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowCatalogue extends OrgAction
{
    use AsAction;
    use WithInertia;


    public function handle(Shop $shop): Shop
    {
        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");

        return $request->user()->hasPermissionTo("products.{$this->shop->id}.view");
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }

    public function htmlResponse(Shop $shop, ActionRequest $request): Response
    {
        $topFamily = $shop->families()->sortByDesc(function ($family) {
            return $family->stats->shop_amount_all;
        })->first();

        $topDepartment = $shop->departments()->sortByDesc(function ($department) {
            return $department->stats->shop_amount_all;
        })->first();

        $topProduct = $shop->products()
            ->join('product_sales_intervals', 'products.id', '=', 'product_sales_intervals.product_id')
            ->select('products.id', 'products.name', 'product_sales_intervals.shop_amount_all')
            ->orderByDesc('product_sales_intervals.shop_amount_all')
            ->first();

        $totalProducts = $shop->stats->number_products;

        $productsWithZeroQuantity = $shop->products()
            ->where('available_quantity', 0)
            ->count();

        $percentageWithZeroQuantity = ($totalProducts > 0)
            ? round(($productsWithZeroQuantity / $totalProducts) * 100, 2)
            : 0;

        return Inertia::render(
            'Org/Catalogue/Catalogue',
            [
                'title'       => __('catalogue'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($shop, $request),
                    'next'     => $this->getNext($shop, $request),
                ],
                'pageHead'    => [
                    'title' => __('Catalogue'),
                    'model' => '',
                    'icon'  => [
                        'title' => __('Catalogue'),
                        'icon'  => 'fal fa-books'
                    ],

                ],
                'dashboard' => [
                    'departments' => [
                        [
                            'label' => __('Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_departments,
                        ],
                        [
                            'label' => __('Current Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_current_departments,
                        ],
                        [
                            'label' => __('Active Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_departments_state_active,
                        ],
                        [
                            'label' => __('Discontinued Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_departments_state_discontinued,
                        ],
                        [
                            'label' => __('Discontinuing Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_departments_state_discontinuing,
                        ],
                        [
                            'label' => __('Departments In Process'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_departments_state_in_process,
                        ],
                        [
                            'label' => __('Top Department'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => DepartmentResource::make($topDepartment),
                        ],
                    ],
                    'sub_departments' => [
                        [
                            'label' => __('Sub Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_sub_departments,
                        ],
                        [
                            'label' => __('Current Sub Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_current_sub_departments,
                        ],
                        [
                            'label' => __('Active Sub Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_sub_departments_state_active,
                        ],
                        [
                            'label' => __('Discontinued Sub Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_sub_departments_state_discontinued,
                        ],
                        [
                            'label' => __('Discontinuing Sub Departments'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_sub_departments_state_discontinuing,
                        ],
                        [
                            'label' => __('Sub Departments In Process'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_sub_departments_state_in_process,
                        ],
                        [
                            'label' => __('Top Department'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $topDepartment,
                        ],
                    ],
                    'families' => [
                        [
                            'label' => __('Families'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_families,
                        ],
                        [
                            'label' => __('Current Families'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_current_families,
                        ],
                        [
                            'label' => __('Active Families'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_families_state_active,
                        ],
                        [
                            'label' => __('Discontinued Families'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_families_state_discontinued,
                        ],
                        [
                            'label' => __('Discontinuing Families'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_families_state_discontinuing,
                        ],
                        [
                            'label' => __('Families In Process'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_families_state_in_process,
                        ],
                        [
                            'label' => __('Top Family'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $topFamily,
                        ],
                        [
                            'label' => __('Top Family'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => FamilyResource::make($topFamily),
                        ],
                    ],
                    'products' => [
                        [
                            'label' => __('Products'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_products,
                        ],
                        [
                            'label' => __('Current Products'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_current_products,
                        ],
                        [
                            'label' => __('Active Products'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_products_state_active,
                        ],
                        [
                            'label' => __('Discontinued Products'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_products_state_discontinued,
                        ],
                        [
                            'label' => __('Discontinuing Products'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_products_state_discontinuing,
                        ],
                        [
                            'label' => __('Products In Process'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_products_state_in_process,
                        ],
                        [
                            'label' => __('Top Product'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => ProductResource::make($topProduct),
                        ],
                        [
                            'out_of_stock' => $percentageWithZeroQuantity
                        ]
                    ],
                    'collections' => [
                        [
                            'label' => __('Collection'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_collections,
                        ],
                        [
                            'label' => __('Collection Categories'),
                            'icon'  => 'fal fa-folder-tree',
                            'value' => $shop->stats->number_collection_categories,
                        ],
                    ],
                ],
                'stats' => [
                    [
                        'label' => __('Department'),
                        'route' => [
                            'name'  => 'grp.org.shops.show.catalogue.departments.index',        // TODO
                            'parameters'    => [
                                'organisation' => $shop->organisation->slug,
                                'shop'         => $shop->slug
                            ]
                        ],
                        'icon'  => 'fal fa-folder-tree',
                        "color" => "#a3e635",
                        'value' => $shop->stats->number_departments,
                    ],
                    [
                        'label' => __('Families'),
                        'route' => [
                            'name'  => 'grp.org.shops.show.catalogue.families.index',        // TODO
                            'parameters'    => [
                                'organisation' => $shop->organisation->slug,
                                'shop'         => $shop->slug
                            ]
                        ],
                        'icon'  => 'fal fa-folder',
                        "color" => "#facc15",
                        'value' => $shop->stats->number_families,
                    ],
                    [
                        'label' => __('Current Products'),
                        'route' => [
                            'name'  => 'grp.org.shops.show.catalogue.products.current_products.index',        // TODO
                            'parameters'    => [
                                'organisation' => $shop->organisation->slug,
                                'shop'         => $shop->slug
                            ]
                        ],
                        'icon'  => 'fal fa-cube',
                        "color" => "#38bdf8",
                        'value' => $shop->stats->number_products,
                        'metas'  =>  [
                            [
                                "value" => "active",
                                "icon" => [
                                    "tooltip" => "active",
                                    "icon" => "fas fa-check-circle",
                                    "class" => "text-green-500"
                                ],
                                "count" => 11843,
                                "label" => "Active"
                            ],
                            [
                                "value" => "discontinuing",
                                "icon" => [
                                    "tooltip" => "discontinuing",
                                    "icon" => "fas fa-times-circle",
                                    "class" => "text-amber-500"
                                ],
                                "count" => 0,
                                "label" => "Discontinuing"
                            ],
                            [
                                "value" => "discontinued",
                                "icon" => [
                                    "tooltip" => "discontinued",
                                    "icon" => "fas fa-times-circle",
                                    "class" => "text-red-500"
                                ],
                                "count" => 29113,
                                "label" => "Discontinued"
                            ]
                        ]
                    ],
                    [
                        'label' => __('Collections'),
                        'route' => [
                            'name'  => 'grp.org.shops.show.catalogue.collections.index',        // TODO
                            'parameters'    => [
                                'organisation' => $shop->organisation->slug,
                                'shop'         => $shop->slug
                            ]
                        ],
                        'icon'  => 'fal fa-album-collection',
                        "color" => "#4f46e5",
                        'value' => $shop->stats->number_collections,
                    ],
                ]

            ]
        );
    }


    public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }


    public function getPrevious(Shop $shop, ActionRequest $request): ?array
    {
        $previous = Shop::where('code', '<', $shop->code)->where('organisation_id', $this->organisation->id)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Shop $shop, ActionRequest $request): ?array
    {
        $next = Shop::where('code', '>', $shop->code)->where('organisation_id', $this->organisation->id)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Shop $shop, string $routeName): ?array
    {
        if (!$shop) {
            return null;
        }

        return match ($routeName) {
            'grp.org.shops.show.catalogue.dashboard' => [
                'label' => $shop->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'shop'         => $shop->slug
                    ]

                ]
            ]
        };
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.catalogue.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Catalogue'),
                        ]
                    ]
                ]
            );
    }
}
