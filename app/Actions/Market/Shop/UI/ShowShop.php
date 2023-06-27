<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:38 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Shop\UI;

use App\Actions\InertiaAction;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Market\ProductCategory\UI\IndexDepartments;
use App\Actions\Market\ProductCategory\UI\IndexFamilies;
use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use App\Enums\UI\ShopTabsEnum;
use App\Http\Resources\Market\DepartmentResource;
use App\Http\Resources\Market\FamilyResource;
use App\Http\Resources\Market\ProductResource;
use App\Http\Resources\Market\ShopResource;
use App\Models\Market\Shop;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowShop extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(Shop $shop): Shop
    {
        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.edit');

        return $request->user()->hasPermissionTo("shops.view");
    }

    public function asController(Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisation($request)->withTab(ShopTabsEnum::values());

        return $this->handle($shop);
    }

    public function htmlResponse(Shop $shop, ActionRequest $request): Response
    {

        $container = [
            'icon'    => ['fal', 'fa-store-alt'],
            'tooltip' => __('Shop'),
            'label'   => Str::possessive($shop->name)
        ];

        return Inertia::render(
            'Market/Shop',
            [
                'title'        => __('shop'),
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->parameters
                ),
                'navigation'   => [
                    'previous' => $this->getPrevious($shop, $request),
                    'next'     => $this->getNext($shop, $request),
                ],
                'pageHead'     => [
                    'title'     => $shop->name,
                    'container' => $container,
                    'actions'   => [

                        [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]

                        ]
                    ],
                    'edit'      => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => $request->route()->originalParameters()
                        ]
                    ] : false,
                ],
                'flatTreeMaps' => [
                    [
                        [
                            'name'  => __('customers'),
                            'icon'  => ['fal', 'fa-user'],
                            'href'  => ['crm.shops.show.customers.index', $shop->slug],
                            'index' => [
                                'number' => $shop->crmStats->number_customers
                            ]
                        ],
                        [
                            'name'  => __('prospects'),
                            'icon'  => ['fal', 'fa-user'],
                            'href'  => ['crm.shops.show.prospects.index', $shop->slug],
                            'index' => [
                                'number' => 'TBD'// $shop->stats->number_customers
                            ]
                        ],
                    ],
                    [
                        [
                            'name'  => __('departments'),
                            'icon'  => ['fal', 'fa-folder-tree'],
                            'href'  => ['shops.show.departments.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_departments
                            ]
                        ],

                        [
                            'name'  => __('families'),
                            'icon'  => ['fal', 'fa-folder'],
                            'href'  => ['shops.show.families.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_families
                            ]
                        ],

                        [
                            'name'  => __('products'),
                            'icon'  => ['fal', 'fa-cube'],
                            'href'  => ['shops.show.products.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_products
                            ]
                        ],
                    ],
                    [
                        [
                            'name'  => __('orders'),
                            'icon'  => ['fal', 'fa-shopping-cart'],
                            'href'  => ['crm.shops.show.orders.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_orders
                            ]
                        ],
                        [
                            'name'  => __('invoices'),
                            'icon'  => ['fal', 'fa-file-invoice'],
                            'href'  => ['crm.shops.show.invoices.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_invoices
                            ]
                        ],
                        [
                            'name'  => __('delivery-notes'),
                            'icon'  => ['fal', 'fa-sticky-note'],
                            'href'  => ['crm.shops.show.delivery-notes.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_deliveries
                            ]
                        ]
                    ]
                ],
                'tabs'         => [
                    'current'    => $this->tab,
                    'navigation' => ShopTabsEnum::navigation()
                ],

                ShopTabsEnum::DEPARTMENTS->value => $this->tab == ShopTabsEnum::DEPARTMENTS->value ?
                    fn () => DepartmentResource::collection(
                        IndexDepartments::run(
                            parent: $shop,
                            prefix: 'departments'
                        )
                    )
                    : Inertia::lazy(fn () => DepartmentResource::collection(
                        IndexDepartments::run(
                            parent: $shop,
                            prefix: 'departments'
                        )
                    )),

                ShopTabsEnum::FAMILIES->value => $this->tab == ShopTabsEnum::FAMILIES->value ?
                    fn () => FamilyResource::collection(
                        IndexFamilies::run(
                            parent: $shop,
                            prefix: 'families'
                        )
                    )
                    : Inertia::lazy(fn () => FamilyResource::collection(
                        IndexFamilies::run(
                            parent: $shop,
                            prefix: 'families'
                        )
                    )),

                ShopTabsEnum::PRODUCTS->value => $this->tab == ShopTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(
                        IndexProducts::run(
                            parent: $shop,
                            prefix: 'products'
                        )
                    )
                    : Inertia::lazy(fn () => ProductResource::collection(
                        IndexProducts::run(
                            parent: $shop,
                            prefix: 'products'
                        )
                    )),

            ]
        )->table(
            IndexDepartments::make()->tableStructure(
                parent: $shop,
                modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'shops.show.departments.create',
                            'parameters' => array_values([$shop->slug])
                        ],
                        'label' => __('departments')
                    ] : false
                ],
                prefix: 'departments'
            )
        )->table(
            IndexFamilies::make()->tableStructure(
                parent: $shop,
                modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'shops.show.families.create',
                            'parameters' => array_values([$shop->slug])
                        ],
                        'label' => __('families')
                    ] : false
                ],
                prefix: 'families'
            )
        )->table(
            IndexProducts::make()->tableStructure(
                parent: $shop,
                modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'shops.show.products.create',
                            'parameters' => array_values([$shop->slug])
                        ],
                        'label' => __('product')
                    ] : false
                ],
                prefix: 'products'
            )
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'           => 'modelWithIndex',
                        'modelWithIndex' => [
                            'index' => [
                                'route' => [
                                    'name' => 'shops.index'
                                ],
                                'label' => __('shops'),
                                'icon'  => 'fal fa-bars'
                            ],
                            'model' => [
                                'route' => [
                                    'name'       => 'shops.show',
                                    'parameters' => [$routeParameters['shop']->slug]
                                ],
                                'label' => $routeParameters['shop']->slug,
                                'icon'  => 'fal fa-bars'
                            ]


                        ],
                        'suffix'         => $suffix,
                    ]
                ]
            );
    }

    public function getPrevious(Shop $shop, ActionRequest $request): ?array
    {
        $previous = Shop::where('code', '<', $shop->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Shop $shop, ActionRequest $request): ?array
    {
        $next = Shop::where('code', '>', $shop->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Shop $shop, string $routeName): ?array
    {
        if (!$shop) {
            return null;
        }

        return match ($routeName) {
            'shops.show' => [
                'label' => $shop->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'shop' => $shop->slug
                    ]

                ]
            ]
        };
    }
}
