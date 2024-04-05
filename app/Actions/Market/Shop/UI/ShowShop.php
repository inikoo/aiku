<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:38 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Shop\UI;

use App\Actions\OrgAction;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Market\ProductCategory\UI\IndexDepartments;
use App\Actions\Market\ProductCategory\UI\IndexFamilies;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Actions\UI\WithInertia;
use App\Enums\UI\ShopTabsEnum;
use App\Http\Resources\Market\DepartmentsResource;
use App\Http\Resources\Market\FamiliesResource;
use App\Http\Resources\Market\ProductsResource;
use App\Http\Resources\Market\ShopResource;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowShop extends OrgAction
{
    use AsAction;
    use WithInertia;



    public function handle(Shop $shop): Shop
    {
        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("shops.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Shop
    {

        $this->initialisation($organisation, $request)->withTab(ShopTabsEnum::values());
        return $this->handle($shop);
    }

    public function htmlResponse(Shop $shop, ActionRequest $request): Response
    {
        return Inertia::render(
            'Market/Shop',
            [
                'title'        => __('shop'),
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'   => [
                    'previous' => $this->getPrevious($shop, $request),
                    'next'     => $this->getNext($shop, $request),
                ],
                'pageHead'     => [
                    'title'   => $shop->name,
                    'icon'    => [
                        'title' => __('Shop'),
                        'icon'  => 'fal fa-store-alt'
                    ],
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('website'),
                            'route' => [
                                'name'       => 'grp.org.shops.show.web.websites.create',
                                'parameters' => $request->route()->originalParameters()
                            ]

                        ],
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                        /*
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'shops.remove',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false
                        */
                    ]
                ],
                'flatTreeMaps' => [
                    [
                        [
                            'name'  => __('customers'),
                            'icon'  => ['fal', 'fa-user'],
                            'href'  => ['grp.org.shops.show.crm.customers.index', $shop->slug],
                            'index' => [
                                'number' => $shop->crmStats->number_customers
                            ]
                        ],
                        [
                            'name'  => __('prospects'),
                            'icon'  => ['fal', 'fa-user'],
                            'href'  => ['grp.crm.shops.show.prospects.index', $shop->slug],
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
                            'href'  => ['grp.crm.shops.show.orders.index', $shop->slug],
                            'index' => [
                                'number' => $shop->salesStats->number_orders
                            ]
                        ],
                        [
                            'name'  => __('invoices'),
                            'icon'  => ['fal', 'fa-file-invoice'],
                            'href'  => ['grp.crm.shops.show.invoices.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_invoices
                            ]
                        ],
                        [
                            'name'  => __('delivery-notes'),
                            'icon'  => ['fal', 'fa-sticky-note'],
                            'href'  => ['grp.crm.shops.show.delivery-notes.index', $shop->slug],
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

                ShopTabsEnum::SHOWCASE->value => $this->tab == ShopTabsEnum::SHOWCASE->value
                    ?
                    fn () => ShopResource::make($shop)
                    : Inertia::lazy(fn () => ShopResource::make($shop)),

                ShopTabsEnum::DEPARTMENTS->value => $this->tab == ShopTabsEnum::DEPARTMENTS->value
                    ?
                    fn () => DepartmentsResource::collection(
                        IndexDepartments::run(
                            parent: $shop,
                            prefix: 'departments'
                        )
                    )
                    : Inertia::lazy(fn () => DepartmentsResource::collection(
                        IndexDepartments::run(
                            parent: $shop,
                            prefix: 'departments'
                        )
                    )),

                ShopTabsEnum::FAMILIES->value => $this->tab == ShopTabsEnum::FAMILIES->value
                    ?
                    fn () => FamiliesResource::collection(
                        IndexFamilies::run(
                            parent: $shop,
                            prefix: 'families'
                        )
                    )
                    : Inertia::lazy(fn () => FamiliesResource::collection(
                        IndexFamilies::run(
                            parent: $shop,
                            prefix: 'families'
                        )
                    )),

                ShopTabsEnum::PRODUCTS->value => $this->tab == ShopTabsEnum::PRODUCTS->value
                    ?
                    fn () => ProductsResource::collection(
                        IndexProducts::run(
                            parent: $shop,
                            prefix: 'products'
                        )
                    )
                    : Inertia::lazy(fn () => ProductsResource::collection(
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
                        'label' => __('department'),
                        'style' => 'create'
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
                        'label' => __('family'),
                        'style' => 'create'
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
                        'label' => __('product'),
                        'style' => 'create'
                    ] : false
                ],
                prefix: 'products'
            )
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->hasPermissionTo('hr.edit'));
        $this->set('canViewUsers', $request->user()->hasPermissionTo('users.view'));
    }

    public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {



        $shop=Shop::where('slug', $routeParameters['shop'])->first();



        return
            array_merge(
                ShowOrganisationDashboard::make()->getBreadcrumbs(Arr::only($routeParameters, 'organisation')),
                [
                    [
                        'type'           => 'modelWithIndex',
                        'modelWithIndex' => [
                            'index' => [
                                'route' => [
                                    'name'       => 'grp.org.shops.index',
                                    'parameters' => Arr::only($routeParameters, 'organisation')
                                ],
                                'label' => __('shops'),
                                'icon'  => 'fal fa-bars'
                            ],
                            'model' => [
                                'route' => [
                                    'name'       => 'grp.org.shops.show.catalogue.dashboard',
                                    'parameters' => Arr::only($routeParameters, ['organisation','shop'])
                                ],
                                'label' => $shop->code,
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
            'grp.org.shops.show.catalogue.dashboard' => [
                'label' => $shop->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'=> $this->organisation->slug,
                        'shop'        => $shop->slug
                    ]

                ]
            ]
        };
    }
}
