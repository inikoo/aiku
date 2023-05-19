<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:38 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Shop\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Marketing\ShopResource;
use App\Models\Marketing\Shop;
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

    public function asController(Shop $shop): Shop
    {
        return $this->handle($shop);
    }

    public function htmlResponse(Shop $shop, ActionRequest $request): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Shop',
            [
                'title'       => __('shop'),
                'breadcrumbs' => $this->getBreadcrumbs($shop),
                'pageHead'    => [
                    'title' => $shop->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => $request->route()->originalParameters()
                        ]
                    ] : false,
                ],
                'shop'            => new ShopResource($shop),
                'flatTreeMaps'    => [
                    [
                        [
                            'name'  => __('customers'),
                            'icon'  => ['fal', 'fa-user'],
                            'href'  => ['shops.show.customers.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_customers
                            ]
                        ],
                        [
                            'name'  => __('prospect'),
                            'icon'  => ['fal', 'fa-user'],
                            'href'  => ['shops.show.prospects.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_customers
                            ]
                        ],
                    ],
                    [
                        [
                            'name'  => __('departments'),
                            'icon'  => ['fal', 'fa-folder-tree'],
                            'href'  => ['shops.show.catalogue.hub.departments.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_departments
                            ]
                        ],
                        /*
                        [
                            'name'  => __('families'),
                            'icon'  => ['fal', 'fa-folder'],
                            'href'  => ['shops.show.catalogue.hub.families.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_families
                            ]
                        ],
                        */
                        [
                            'name'  => __('products'),
                            'icon'  => ['fal', 'fa-cube'],
                            'href'  => ['shops.show.catalogue.hub.products.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_products
                            ]
                        ],
                    ],
                    [
                        [
                            'name'  => __('orders'),
                            'icon'  => ['fal', 'fa-shopping-cart'],
                            'href'  => ['shops.show.orders.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_orders
                            ]
                        ],
                        [
                            'name'  => __('invoices'),
                            'icon'  => ['fal', 'fa-file-invoice'],
                            'href'  => ['shops.show.invoices.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_invoices
                            ]
                        ],
                        [
                            'name'  => __('delivery-notes'),
                            'icon'  => ['fal', 'fa-sticky-note'],
                            'href'  => ['shops.show.delivery-notes.index', $shop->slug],
                            'index' => [
                                'number' => $shop->stats->number_deliveries
                            ]
                        ]
                    ]
                ]
            ]
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


    public function getBreadcrumbs(Shop $shop, $suffix = null): array
    {
        return
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'           => 'modelWithIndex',
                        'modelWithIndex' => [
                            'index'=> [
                                'route' => [
                                    'name' => 'shops.index'
                                ],
                                'label' => __('shops'),
                                'icon'  => 'fal fa-bars'
                            ],
                            'model'=> [
                                'route' => [
                                    'name'      => 'shops.show',
                                    'parameters'=> [$shop->slug]
                                ],
                                'label' => $shop->code,
                                'icon'  => 'fal fa-bars'
                            ]



                        ],
                        'suffix' => $suffix,
                    ]
                ]
            );
    }
}
