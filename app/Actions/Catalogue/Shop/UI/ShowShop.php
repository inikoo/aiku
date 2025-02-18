<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:38 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Shop\UI;

use App\Actions\Helpers\History\UI\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Actions\UI\WithInertia;
use App\Enums\UI\Catalogue\ShopTabsEnum;
use App\Http\Resources\Catalogue\ShopResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\Catalogue\Shop;
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
        $this->canEdit   = $request->user()->authTo("products.{$this->shop->id}.edit");
        $this->canDelete = $request->user()->authTo("products.{$this->shop->id}.edit");

        return $request->user()->authTo(["products.{$this->shop->id}.view", "accounting.{$this->shop->organisation_id}.view"]);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisationFromShop($shop, $request)->withTab(ShopTabsEnum::values());

        return $this->handle($shop);
    }

    public function htmlResponse(Shop $shop, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Shop',
            [
                'title'       => __('shop'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($shop, $request),
                    'next'     => $this->getNext($shop, $request),
                ],

                'pageHead' => [
                    'title'   => $shop->name,
                    'icon'    => [
                        'title' => __('Shop'),
                        'icon'  => 'fal fa-store-alt'
                    ],
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'label' => __('settings'),
                            'icon'  => 'fal fa-sliders-h',
                            'route' => [
                                'name'       => 'grp.org.shops.show.settings.edit',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,

                    ]
                ],

                'flatTreeMaps' => [
                    [
                        [
                            'name'  => __('customers'),
                            'icon'  => ['fal', 'fa-user'],
                            'route' => ['grp.org.shops.show.crm.customers.index', $shop->slug],
                            'index' => [
                                'number' => $shop->crmStats->number_customers
                            ]
                        ],
                        [
                            'name'  => __('prospects'),
                            'icon'  => ['fal', 'fa-user'],
                            'route' => ['grp.crm.shops.show.prospects.index', $shop->slug],
                            'index' => [
                                'number' => 'TBD'// $shop->stats->number_customers
                            ]
                        ],
                    ],
                    [
                        [
                            'name'  => __('departments'),
                            'icon'  => ['fal', 'fa-folder-tree'],
                            'route' => ['shops.show.departments.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_departments
                            ]
                        ],

                        [
                            'name'  => __('families'),
                            'icon'  => ['fal', 'fa-folder'],
                            'route' => ['shops.show.families.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_families
                            ]
                        ],

                        [
                            'name'  => __('products'),
                            'icon'  => ['fal', 'fa-cube'],
                            'route' => ['shops.show.products.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_products
                            ]
                        ],
                    ],
                    [
                        [
                            'name'  => __('orders'),
                            'icon'  => ['fal', 'fa-shopping-cart'],
                            'route' => ['grp.crm.shops.show.orders.index', $shop->slug],
                            'index' => [
                                'number' => $shop->orderingStats->number_orders
                            ]
                        ],
                        [
                            'name'  => __('invoices'),
                            'icon'  => ['fal', 'fa-file-invoice'],
                            'route' => ['grp.crm.shops.show.invoices.index', $shop->slug],
                            'index' => [
                                'number' => $shop->orderingStats->number_invoices
                            ]
                        ],
                        [
                            'name'  => __('delivery-notes'),
                            'icon'  => ['fal', 'fa-sticky-note'],
                            'route' => ['grp.crm.shops.show.delivery-notes.index', $shop->slug],
                            'index' => [
                                'number' => $shop->orderingStats->number_deliveries
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

                ShopTabsEnum::HISTORY->value => $this->tab == ShopTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($shop))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($shop)))


            ]
        )->table(
            IndexHistory::make()->tableStructure(
                prefix: ShopTabsEnum::HISTORY->value
            )
        );
    }


    public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $shop = Shop::where('slug', $routeParameters['shop'])->first();

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
                                'label' => __('Shops'),
                                'icon'  => 'fal fa-bars'
                            ],
                            'model' => [
                                'route' => [
                                    'name'       => 'grp.org.shops.show.dashboard',
                                    'parameters' => Arr::only($routeParameters, ['organisation', 'shop'])
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
            'grp.org.shops.show.dashboard' => [
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


}
