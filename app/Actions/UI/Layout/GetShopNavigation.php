<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jan 2024 01:18:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

use App\Models\Market\Shop;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetShopNavigation
{
    use AsAction;

    public function handle(Shop $shop, User $user): array
    {


        $navigation = [];


        if ($user->hasPermissionTo("shops.$shop->slug.view")) {
            $navigation['shops'] = [
                'scope' => 'shops',
                'icon'  => ['fal', 'fa-store-alt'],

                'label' => [
                    'all'      => __('Shops'),
                    'selected' => __('Shop')

                ],
                'route' => [
                    'all'      => 'grp.shops.index',
                    'selected' => 'shops.show'
                ],


                'topMenu' => [

                    'dropdown' => [
                        'links' => [

                            [
                                'tooltip' => __('shops'),
                                'icon'    => ['fal', 'fa-store-alt'],
                                'route'   => [
                                    'all'      => 'grp.shops.index',
                                    'selected' => 'grp.shops.show',

                                ],
                                'label'   => [
                                    'all'      => __('Shops'),
                                    'selected' => __('Shop'),

                                ]
                            ],

                            [
                                'label'   => __('departments'),
                                'tooltip' => __('Departments'),
                                'icon'    => ['fal', 'fa-folder-tree'],
                                'route'   => [
                                    'all'      => 'grp.shops.departments.index',
                                    'selected' => 'grp.shops.show.departments.index',
                                ]
                            ],
                            [
                                'label'   => __('families'),
                                'tooltip' => __('Families'),
                                'icon'    => ['fal', 'fa-folder'],
                                'route'   => [
                                    'all'      => 'grp.shops.families.index',
                                    'selected' => 'grp.shops.show.families.index',
                                ]
                            ],
                            [
                                'label'   => __('products'),
                                'tooltip' => __('Products'),
                                'icon'    => ['fal', 'fa-cube'],
                                'route'   => [
                                    'all'      => 'grp.shops.products.index',
                                    'selected' => 'grp.shops.show.products.index',
                                ]
                            ],
                        ]
                    ],


                ],
            ];
        }

        if ($user->hasPermissionTo("website.$shop->slug.view")) {
            $navigation['web'] = [
                'scope' => 'websites',
                'icon'  => ['fal', 'fa-globe'],
                'label' => [
                    'all'      => __('Websites'),
                    'selected' => __('Website')

                ],
                'route' => [
                    'all'      => 'grp.web.dashboard',
                    'selected' => 'grp.web.websites.dashboard'
                ],



                'topMenu' => [
                    'subSections' => [
                        [
                            'icon'  => ['fal', 'fa-chart-network'],
                            'route' => [
                                'name' => 'grp.web.dashboard',
                            ]
                        ],
                    ],
                    'dropdown' => [

                        'links' => [

                            [
                                'tooltip' => __('websites'),
                                'icon'    => ['fal', 'fa-globe'],
                                'route'   => [
                                    'all'      => 'grp.web.websites.index',
                                    'selected' => 'grp.web.websites.show',

                                ],
                                'label'   => [
                                    'all'      => __('Websites'),
                                    'selected' => __('Website'),

                                ]
                            ],
                            [
                                'label'   => __('webpages'),
                                'tooltip' => __('Webpages'),
                                'icon'    => ['fal', 'fa-browser'],
                                'route'   => [
                                    'all'      => ['grp.web.webpages.index'],
                                    'selected' => ['grp.web.websites.show.webpages.index'],

                                ]
                            ],

                        ]
                    ]
                ]


            ];
        }

        if ($user->hasPermissionTo("marketing.view")) {
            $navigation['marketing'] = [
                'scope'   => 'shops',
                'label'   => __('Marketing'),
                'icon'    => ['fal', 'fa-bullhorn'],
                'route'   => 'grp.marketing.hub',
                'topMenu' => [
                    'subSections' => [],
                    'dropdown'    => [

                        'links' => []
                    ]
                ]
            ];
        }

        if ($user->hasPermissionTo("crm.$shop->slug.view")) {
            $navigation['crm'] = [
                'scope' => 'shops',
                'label' => __('Customers'),
                'icon'  => ['fal', 'fa-user'],

                'route'   => [
                    'all'      => 'grp.crm.dashboard',
                    'selected' => 'grp.crm.shops.show.dashboard',
                ],
                'topMenu' => [
                    'dropdown' => [
                        'links' => [
                            [
                                'tooltip' => __('Dashboard'),
                                'icon'    => ['fal', 'fa-chart-network'],
                                'route'   =>
                                    [
                                        'all'      => ['grp.crm.dashboard'],
                                        'selected' => ['grp.crm.shops.show.dashboard'],
                                    ]
                            ],
                            [
                                'label'   => __('customers'),
                                'tooltip' => __('Customers'),
                                'icon'    => ['fal', 'fa-user'],
                                'route'   => [
                                    'all'      => ['grp.crm.customers.index'],
                                    'selected' => ['grp.crm.shops.show.customers.index'],

                                ]
                            ],
                            [
                                'label'   => __('prospects'),
                                'tooltip' => __('Prospects'),
                                'icon'    => ['fal', 'fa-user-plus'],
                                'route'   => [
                                    'all'      => ['grp.crm.prospects.index'],
                                    'selected' => ['grp.crm.shops.show.prospects.index'],

                                ]
                            ],


                        ]
                    ]
                ]

            ];
        }

        if ($user->hasPermissionTo("oms.$shop->slug.view")) {
            $navigation['oms'] = [
                'scope'   => 'shops',
                'label'   => __('Orders'),
                'icon'    => ['fal', 'fa-shopping-cart'],
                'route'   => [
                    'all'      => 'grp.oms.dashboard',
                    'selected' => 'grp.oms.shops.show.dashboard',
                ],
                'topMenu' => [
                    'dropdown' => [
                        'links' => [
                            [
                                'label'   => 'OMS',
                                'tooltip' => 'OMS',
                                'icon'    => ['fal', 'fa-tasks-alt'],
                                'route'   =>
                                    [
                                        'all'      => ['grp.oms.dashboard'],
                                        'selected' => ['grp.oms.shops.show.dashboard'],

                                    ]
                            ],

                            [
                                'label'   => __('orders'),
                                'tooltip' => __('Orders'),
                                'icon'    => ['fal', 'fa-shopping-cart'],
                                'route'   => [
                                    'all'      => ['grp.oms.orders.index'],
                                    'selected' => ['grp.oms.shops.show.orders.index'],

                                ]
                            ],
                            [
                                'label'   => __('delivery notes'),
                                'tooltip' => __('Delivery notes'),
                                'icon'    => ['fal', 'fa-truck'],
                                'route'   => [
                                    'all'      => ['grp.oms.delivery-notes.index'],
                                    'selected' => ['grp.oms.shops.show.delivery-notes.index'],

                                ]
                            ],
                            [
                                'label'   => __('invoices'),
                                'tooltip' => __('Invoices'),
                                'icon'    => ['fal', 'fa-file-invoice-dollar'],
                                'route'   => [
                                    'all'      => ['grp.oms.invoices.index'],
                                    'selected' => ['grp.oms.shops.show.invoices.index'],

                                ]
                            ],

                        ]
                    ]

                ]
            ];
        }


        return $navigation;
    }
}
