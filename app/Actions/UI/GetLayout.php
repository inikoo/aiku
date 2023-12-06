<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 22:03:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use App\Http\Resources\UI\ShopsNavigationResource;
use App\Http\Resources\UI\WarehousesNavigationResource;
use App\Http\Resources\UI\WebsitesNavigationResource;
use App\Models\Inventory\Warehouse;
use App\Models\Market\Shop;
use App\Models\SysAdmin\User;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLayout
{
    use AsAction;

    public function handle(User $user): array
    {

        $groupNavigation=[];


        if ($user->hasPermissionTo('supply-chain.view')) {
            $navigation['procurement'] = [
                'label'   => __('procurement'),
                'icon'    => ['fal', 'fa-box-usd'],
                'route'   => 'grp.procurement.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('agents'),
                            'icon'  => ['fal', 'fa-people-arrows'],
                            'route' => [
                                'name' => 'grp.procurement.agents.index',

                            ]
                        ],
                        [
                            'label' => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
                            'route' => [
                                'name' => 'grp.procurement.suppliers.index',

                            ]
                        ],

                    ]
                ]
            ];
        }


        if ($user->hasPermissionTo('sysadmin.view')) {
            $navigation['sysadmin'] = [
                'label'   => __('sysadmin'),
                'icon'    => ['fal', 'fa-users-cog'],
                'route'   => 'grp.sysadmin.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('users'),
                            'icon'  => ['fal', 'fa-terminal'],
                            'route' => [
                                'name' => 'grp.sysadmin.users.index',

                            ]
                        ],
                        [
                            'label' => __('guests'),
                            'icon'  => ['fal', 'fa-user-alien'],
                            'route' => [
                                'name' => 'grp.sysadmin.guests.index',

                            ]
                        ],
                        [
                            'label' => __('system settings'),
                            'icon'  => ['fal', 'fa-cog'],
                            'route' => [
                                'name' => 'grp.sysadmin.settings.edit',

                            ]
                        ],
                    ]
                ]
            ];
        }


        $navigation = [];

        if ($user->hasPermissionTo('org-business-intelligence')) {
            $navigation['business_intelligence'] = [
                'label' => __('Business Intelligence'),

                'scope' => 'shops',
                'icon'  => ['fal', 'fa-lightbulb'],

                'route' => [
                    'all'      => 'grp.business_intelligence.dashboard',
                    'selected' => 'grp.business_intelligence.shops.show.dashboard'
                ],

                'topMenu' => [

                    'dropdown' => [
                        'links' => [
                            [
                                'label'   => __('dashboard'),
                                'tooltip' => __('Dashboard'),


                                'icon'  => ['fal', 'fa-tasks-alt'],
                                'route' =>
                                    [
                                        'all'      => ['grp.business_intelligence.dashboard'],
                                        'selected' => ['grp.business_intelligence.shops.show.dashboard'],

                                    ]
                            ],


                        ]
                    ]
                ],
            ];
        }

        if ($user->hasPermissionTo('shops.view')) {
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

        if ($user->hasPermissionTo('websites.view')) {
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

        if ($user->hasPermissionTo('marketing.view')) {
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

        if ($user->hasPermissionTo('crm.view')) {
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

        if ($user->hasPermissionTo('oms.view')) {
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

        if ($user->hasPermissionTo('dispatch.view')) {
            $navigation['dispatch'] = [
                'label'   => __('Dispatch'),
                'icon'    => ['fal', 'fa-conveyor-belt-alt'],
                'route'   => 'grp.dispatch.hub',
                'topMenu' => [
                    'subSections' => []
                ]
            ];
        }

        if ($user->hasPermissionTo('inventory.view')) {
            $navigation['inventory'] = [
                'scope'   => 'warehouses',
                'label'   => __('inventory'),
                'icon'    => ['fal', 'fa-inventory'],
                'route'   => [
                    'all'      => 'grp.inventory.dashboard',
                    'selected' => 'grp.inventory.warehouses.show',
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'icon'  => ['fal', 'fa-chart-network'],
                            'route' => [
                                'name' => 'grp.inventory.dashboard',
                            ]
                        ],

                        [
                            'label' => __('SKUs'),
                            'icon'  => ['fal', 'fa-box'],
                            'route' => [
                                'name' => 'inventory.stocks.index',
                            ]
                        ],
                        [
                            'label'   => __('SKUs Families'),
                            'tooltip' => __('SKUs families'),
                            'icon'    => ['fal', 'fa-boxes-alt'],
                            'route'   => [
                                'name' => 'inventory.stock-families.index',
                            ]
                        ],

                    ],

                    'dropdown' => [
                        'links' => [


                            [
                                'tooltip' => __('warehouses'),
                                'icon'    => ['fal', 'fa-store-alt'],
                                'route'   => [
                                    'all'      => 'inventory.warehouses.index',
                                    'selected' => 'inventory.warehouses.show',

                                ],
                                'label'   => [
                                    'all'      => __('Warehouses'),
                                    'selected' => __('Warehouse'),

                                ]
                            ],


                            [
                                'label'   => __('warehouse areas'),
                                'tooltip' => __('Warehouse Areas'),
                                'icon'    => ['fal', 'fa-map-signs'],
                                'route'   => [
                                    'all'      => 'inventory.warehouse-areas.index',
                                    'selected' => 'inventory.warehouses.show.warehouse-areas.index',

                                ]
                            ],
                            [
                                'label'   => __('locations'),
                                'tooltip' => __('Locations'),
                                'icon'    => ['fal', 'fa-inventory'],
                                'route'   => [
                                    'all'      => 'inventory.locations.index',
                                    'selected' => 'inventory.warehouses.show.locations.index',

                                ]
                            ],
                        ]
                    ]

                ]
            ];
        }

        if ($user->hasPermissionTo('fulfilment.view')
            //  and app('currentTenant')->marketStats->number_shops_subtype_fulfilment
        ) {
            $navigation['fulfilment'] = [
                'label'   => __('fulfilment'),
                'icon'    => ['fal', 'fa-dolly-flatbed-alt'],
                'route'   => 'grp.fulfilment.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'icon'  => ['fal', 'fa-chart-network'],
                            'route' => [
                                'name' => 'grp.fulfilment.dashboard',
                            ]
                        ],

                        [
                            'label' => __('customers'),
                            'icon'  => ['fal', 'fa-user-tie'],
                            'route' => [
                                'name' => 'grp.fulfilment.customers.index',
                            ]
                        ],
                        [
                            'label'   => __('stored items'),
                            'tooltip' => __('stored items'),
                            'icon'    => ['fal', 'fa-narwhal'],
                            'route'   => [
                                'name' => 'grp.fulfilment.stored-items.index',
                            ]
                        ],
                        [
                            'label'   => __('orders'),
                            'tooltip' => __('orders'),
                            'icon'    => ['fal', 'fa-business-time'],
                            'route'   => [
                                'name' => 'grp.fulfilment.orders.index',
                            ]
                        ],

                    ],



                ]
            ];
        }

        if ($user->hasPermissionTo('production.view')) {
            $navigation['production'] = [
                'label'   => __('production'),
                'icon'    => ['fal', 'fa-industry'],
                'route'   => 'grp.production.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('raw materials'),
                            'icon'  => ['fal', 'fa-network-wired'],
                            'route' => [
                                'name' => 'grp.hr.job-positions.index',

                            ]
                        ],
                        [
                            'label' => __('employees'),
                            'icon'  => ['fal', 'fa-terminal'],
                            'route' => [
                                'name' => 'grp.hr.employees.index',

                            ]
                        ],
                        [
                            'label' => __('calendar'),
                            'icon'  => ['fal', 'fa-calendar'],
                            'route' => [
                                'name' => 'grp.hr.calendars.index',

                            ]
                        ],
                        [
                            'label' => __('time sheets'),
                            'icon'  => ['fal', 'fa-stopwatch'],
                            'route' => [
                                'name' => 'grp.hr.time-sheets.index',

                            ]
                        ],
                        [
                            'label' => __('clocking machines'),
                            'icon'  => ['fal', 'fa-chess-clock'],
                            'route' => [
                                'name' => 'grp.hr.clocking-machines.index',

                            ]
                        ]
                    ]
                ]

            ];
        }

        if ($user->hasPermissionTo('procurement.view')) {
            $navigation['procurement'] = [
                'label'   => __('procurement'),
                'icon'    => ['fal', 'fa-box-usd'],
                'route'   => 'grp.procurement.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('agents'),
                            'icon'  => ['fal', 'fa-people-arrows'],
                            'route' => [
                                'name' => 'grp.procurement.agents.index',

                            ]
                        ],
                        [
                            'label' => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
                            'route' => [
                                'name' => 'grp.procurement.suppliers.index',

                            ]
                        ],
                        [
                            'label' => __('purchase orders'),
                            'icon'  => ['fal', 'fa-clipboard-list'],
                            'route' => [
                                'name' => 'grp.procurement.purchase-orders.index',

                            ]
                        ],
                    ]
                ]
            ];
        }

        if ($user->hasPermissionTo('accounting.view')) {
            $navigation['accounting'] = [
                'scope' => 'shops',
                'label' => __('Accounting'),
                'icon'  => ['fal', 'fa-abacus'],
                'route' => [
                    'all'      => 'grp.accounting.dashboard',
                    'selected' => 'grp.accounting.dashboard',
                ],


                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('Payment accounts'),
                            'icon'  => ['fal', 'fa-money-check-alt'],
                            'route' => [
                                'name' => 'grp.accounting.payment-accounts.index',
                            ]
                        ],
                    ],
                    'dropdown'    => [
                        'links' => [

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

        if ($user->hasPermissionTo('hr.view')) {
            $navigation['hr'] = [
                'label'   => __('human resources'),
                'icon'    => ['fal', 'fa-user-hard-hat'],
                'route'   => 'grp.hr.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('job positions'),
                            'icon'  => ['fal', 'fa-network-wired'],
                            'route' => [
                                'name' => 'grp.hr.job-positions.index',

                            ]
                        ],
                        [
                            'label' => __('employees'),
                            'icon'  => ['fal', 'fa-user-hard-hat'],
                            'route' => [
                                'name' => 'grp.hr.employees.index',

                            ]
                        ],
                        [
                            'label' => __('calendar'),
                            'icon'  => ['fal', 'fa-calendar'],
                            'route' => [
                                'name' => 'grp.hr.calendars.index',

                            ]
                        ],
                        [
                            'label' => __('time sheets'),
                            'icon'  => ['fal', 'fa-stopwatch'],
                            'route' => [
                                'name' => 'grp.hr.time-sheets.index',

                            ]
                        ],
                        [
                            'label' => __('working place'),
                            'icon'  => ['fal', 'fa-building'],
                            'route' => [
                                'name' => 'grp.hr.working-places.index',

                            ]
                        ]
                    ]
                ]
            ];
        }




        return [
            'groupNavigation'      => $groupNavigation,
            'navigation'           => $navigation,
            'shopsInDropDown'      => ShopsNavigationResource::collection(Shop::all()),
            'websitesInDropDown'   => WebsitesNavigationResource::collection(Website::all()),
            'warehousesInDropDown' => WarehousesNavigationResource::collection(Warehouse::all()),

        ];
    }
}
