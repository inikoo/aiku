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
use App\Models\Auth\User;
use App\Models\Inventory\Warehouse;
use App\Models\Market\Shop;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLayout
{
    use AsAction;

    public function handle(User $user): array
    {
        $tenant              = app('currentTenant');
        $shopCount           = $tenant->marketingStats->number_shops;
        $currentShopInstance = null;
        if ($shopCount == 1) {
            $currentShopInstance = Shop::first();
        }


        $shops = ShopsNavigationResource::collection(Shop::with('website')->get()->all());


        $websites = WebsitesNavigationResource::collection(Website::with('shop')->get()->all());


        $selectedWarehouse = 0;
        $warehouses        = Warehouse::all();
        $numberWarehouses  = $warehouses->count();
        if ($numberWarehouses > 0) {
            $selectedWarehouse = Warehouse::first()->slug;
        }

        $navigation = [];

        if ($user->can('business-intelligence.view')) {
            $navigation['business_intelligence'] = [
                'name'         => __('Business Intelligence'),
                'icon'         => ['fal', 'fa-lightbulb'],
                'route'        => 'business_intelligence.dashboard',
                'routeOption'  => 'business_intelligence.shop.dashboard',
                'labelShowAll' => __('All shops'),
                'currentData'  => [
                    'slug' => null,
                    'name' => __('All shops'),
                    'code' => __('All')
                ],
                'topMenu'      => [

                    'dropdown' => [
                        'type'        => 'shops',
                        'options'     => $shops,
                        'subsections' => [
                            [
                                'label'   => __('dashboard'),
                                'tooltip' => __('Dashboard'),


                                'icon'  => ['fal', 'fa-tasks-alt'],
                                'route' =>
                                    [
                                        'all'      => ['business_intelligence.dashboard'],
                                        'selected' => ['business_intelligence.shop.dashboard'],

                                    ]
                            ],


                        ]
                    ]
                ],
            ];
        }

        if ($user->can('shops.view')) {
            $navigation['shops'] = [
                'name'         => __('Shops'),
                'icon'         => ['fal', 'fa-store-alt'],
                'route'        => 'shops.index',
                'routeOption'  => 'shops.show',
                'labelShowAll' => __('All shops'),
                'currentData'  => [
                    'slug' => null,
                    'name' => __('All shops'),
                    'code' => __('All')
                ],
                'topMenu'      => [
                    'dropdown' => [
                        'type'        => 'shops',
                        'options'     => $shops,
                        'subsections' => [

                            [
                                'hideOnSelected' => true,
                                'label'          => __('shops'),
                                'tooltip'        => __('shops'),
                                'icon'           => ['fal', 'fa-store-alt'],
                                'route'          => [
                                    'all'      => ['shops.index'],
                                    'selected' => ['shops.show'],

                                ]
                            ],

                            [
                                'label'   => __('departments'),
                                'tooltip' => __('Departments'),
                                'icon'    => ['fal', 'fa-folders'],
                                'route'   => [
                                    'all'      => ['shops.departments.index'],
                                    'selected' => ['shops.show.departments.index'],
                                ]
                            ],
                            [
                                'label'   => __('families'),
                                'tooltip' => __('Families'),
                                'icon'    => ['fal', 'fa-folder'],
                                'route'   => [
                                    'all'      => ['shops.families.index'],
                                    'selected' => ['shops.show.families.index'],
                                ]
                            ],
                            [
                                'label'   => __('products'),
                                'tooltip' => __('Products'),
                                'icon'    => ['fal', 'fa-cube'],
                                'route'   => [
                                    'all'      => ['shops.products.index'],
                                    'selected' => ['shops.show.products.index'],
                                ]
                            ],
                        ]
                    ]
                ],
            ];
        }


        if ($user->can('websites.view')) {
            $navigation['websites'] = [
                'name'         => __('Websites'),
                'icon'         => ['fal', 'fa-globe'],
                'route'        => 'websites.dashboard',
                'routeOption'  => 'websites.show',
                'labelShowAll' => __('All websites'),
                'currentData'  => [
                    'slug' => null,
                    'name' => __('All websites'),
                    'code' => __('All')
                ],
                'topMenu'      => [

                    'dropdown' => [

                        'type'        => 'websites',
                        'options'     => $websites,
                        'subsections' => [
                            [
                                'label'   => __('dashboard'),
                                'tooltip' => __('Dashboard'),


                                'icon'  => ['fal', 'fa-globe'],
                                'route' =>
                                    [
                                        'all'      => ['websites.dashboard'],
                                        'selected' => ['websites.show'],

                                    ]
                            ],
                            [
                                'label'   => __('webpages'),
                                'tooltip' => __('Webpages'),
                                'icon'    => ['fal', 'fa-browser'],
                                'route'   => [
                                    'all'      => ['websites.webpages.index'],
                                    'selected' => ['websites.show.webpages.index'],

                                ]
                            ],

                        ]
                    ]
                ],

            ];
        }

        if ($user->can('marketing.view')) {
            $navigation['marketing'] = [
                'name'  => __('Marketing'),
                'icon'  => ['fal', 'fa-bullhorn'],
                'route' => 'marketing.hub'
            ];
        }


        if ($user->can('crm.view')) {
            $navigation['crm'] = [
                'name'         => _('Customers'),
                'icon'         => ['fal', 'fa-user'],
                'route'        => 'crm.dashboard',
                'routeOption'  => 'crm.shops.show.dashboard',
                'labelShowAll' => __('All shops'),
                'currentData'  => [
                    'slug' => null,
                    'name' => __('All shops'),
                    'code' => __('All')
                ],
                'topMenu'      => [

                    'dropdown' => [
                        'type'        => 'shops',
                        'options'     => $shops,
                        'subsections' => [
                            [
                                'label'   => __('dashboard'),
                                'tooltip' => __('Dashboard'),


                                'icon'  => ['fal', 'fa-tasks-alt'],
                                'route' =>
                                    [
                                        'all'      => ['crm.dashboard'],
                                        'selected' => ['crm.shops.show.dashboard'],

                                    ]
                            ],
                            [
                                'label'   => __('customers'),
                                'tooltip' => __('Customers'),
                                'icon'    => ['fal', 'fa-user'],
                                'route'   => [
                                    'all'      => ['crm.customers.index'],
                                    'selected' => ['crm.shops.show.customers.index'],

                                ]
                            ],
                            [
                                'label'   => __('prospects'),
                                'tooltip' => __('Prospects'),
                                'icon'    => ['fal', 'fa-user-plus'],
                                'route'   => [
                                    'all'      => ['crm.prospects.index'],
                                    'selected' => ['crm.shops.show.prospects.index'],

                                ]
                            ],


                        ]
                    ]
                ],
            ];
        }

        if ($user->can('oms.view')) {
            $navigation['oms'] = [
                'name'         => _('Orders'),
                'icon'         => ['fal', 'fa-shopping-cart'],
                'route'        => 'oms.dashboard',
                'routeOption'  => 'oms.shops.show.dashboard',
                'labelShowAll' => __('All shops'),
                'currentData'  => [
                    'slug' => null,
                    'name' => __('All shops'),
                    'code' => __('All')
                ],
                'topMenu'      => [

                    'dropdown' => [
                        'type'        => 'shops',
                        'options'     => $shops,
                        'subsections' => [
                            [
                                'label'   => 'OMS',
                                'tooltip' => 'OMS',


                                'icon'  => ['fal', 'fa-tasks-alt'],
                                'route' =>
                                    [
                                        'all'      => ['oms.dashboard'],
                                        'selected' => ['oms.shops.show.dashboard'],

                                    ]
                            ],

                            [
                                'label'   => __('orders'),
                                'tooltip' => __('Orders'),
                                'icon'    => ['fal', 'fa-shopping-cart'],
                                'route'   => [
                                    'all'      => ['oms.orders.index'],
                                    'selected' => ['oms.shops.show.orders.index'],

                                ]
                            ],
                            [
                                'label'   => __('delivery notes'),
                                'tooltip' => __('Delivery notes'),
                                'icon'    => ['fal', 'fa-truck'],
                                'route'   => [
                                    'all'      => ['oms.delivery-notes.index'],
                                    'selected' => ['oms.shops.show.delivery-notes.index'],

                                ]
                            ],
                            [
                                'label'   => __('invoices'),
                                'tooltip' => __('Invoices'),
                                'icon'    => ['fal', 'fa-file-invoice-dollar'],
                                'route'   => [
                                    'all'      => ['oms.invoices.index'],
                                    'selected' => ['oms.shops.show.invoices.index'],

                                ]
                            ],

                        ]
                    ]
                ],
            ];
        }


        if ($user->can('dispatch.view')) {
            $navigation['dispatch'] = [
                'name'  => __('Dispatch'),
                'icon'  => ['fal', 'fa-conveyor-belt-alt'],
                'route' => 'dispatch.hub'
            ];
        }

        if ($user->can('inventory.view')) {
            $navigation['inventory'] = [
                'name'         => __('inventory'),
                'icon'         => ['fal', 'fa-inventory'],
                'route'        => 'inventory.dashboard',
                'routeOption'  => 'inventory.warehouses.show',
                'labelShowAll' => __('All warehouses'),
                'currentData'  => [
                    'slug' => null,
                    'name' => __('All warehouses'),
                    'code' => __('All')
                ],
                'topMenu'      => [
                    'subSections' => [
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
                    'dropdown'    => [

                        'type'        => 'warehouses',
                        'options'     => WarehousesNavigationResource::collection($warehouses),
                        'selected'    => $selectedWarehouse,
                        'optionRoute' => 'inventory.warehouses.show',
                        'subsections' => [
                            [
                                'label'           => __('warehouse'),
                                'tooltip'         => __('Warehouse'),
                                'labelSelected'   => __('warehouse'),
                                'tooltipSelected' => __('Warehouse'),
                                'icon'            => ['fal', 'fa-warehouse'],

                                'route' =>
                                    [
                                        'all'      => 'inventory.warehouses.index',
                                        'selected' => 'inventory.warehouses.show',

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
                ],
            ];
        }


        if ($user->can('production.view')) {
            $navigation['production'] = [
                'name'    => __('production'),
                'icon'    => ['fal', 'fa-industry'],
                'route'   => 'production.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('raw materials'),
                            'icon'  => ['fal', 'fa-network-wired'],
                            'route' => [
                                'name' => 'hr.job-positions.index',

                            ]
                        ],
                        [
                            'label' => __('employees'),
                            'icon'  => ['fal', 'fa-terminal'],
                            'route' => [
                                'name' => 'hr.employees.index',

                            ]
                        ],
                        [
                            'label' => __('calendar'),
                            'icon'  => ['fal', 'fa-calendar'],
                            'route' => [
                                'name' => 'hr.calendars.index',

                            ]
                        ],
                        [
                            'label' => __('time sheets'),
                            'icon'  => ['fal', 'fa-stopwatch'],
                            'route' => [
                                'name' => 'hr.time-sheets.index',

                            ]
                        ],
                        [
                            'label' => __('clocking machines'),
                            'icon'  => ['fal', 'fa-chess-clock'],
                            'route' => [
                                'name' => 'hr.clocking-machines.index',

                            ]
                        ]
                    ]
                ]

            ];
        }

        if ($user->can('procurement.view')) {
            $navigation['procurement'] = [
                'name'    => __('procurement'),
                'icon'    => ['fal', 'fa-box-usd'],
                'route'   => 'procurement.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('agents'),
                            'icon'  => ['fal', 'fa-people-arrows'],
                            'route' => [
                                'name' => 'procurement.agents.index',

                            ]
                        ],
                        [
                            'label' => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
                            'route' => [
                                'name' => 'procurement.suppliers.index',

                            ]
                        ],
                        [
                            'label' => __('purchase orders'),
                            'icon'  => ['fal', 'fa-clipboard-list'],
                            'route' => [
                                'name' => 'procurement.purchase-orders.index',

                            ]
                        ],
                    ]
                ]
            ];
        }
        if ($user->can('accounting.view')) {
            $navigation['accounting'] = [
                'name'  => __('Accounting'),
                'icon'  => ['fal', 'fa-abacus'],
                'route' => 'accounting.dashboard'
            ];
        }


        if ($user->can('hr.view')) {
            $navigation['hr'] = [
                'name'    => __('human resources'),
                'icon'    => ['fal', 'fa-user-hard-hat'],
                'route'   => 'hr.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('job positions'),
                            'icon'  => ['fal', 'fa-network-wired'],
                            'route' => [
                                'name' => 'hr.job-positions.index',

                            ]
                        ],
                        [
                            'label' => __('employees'),
                            'icon'  => ['fal', 'fa-user-hard-hat'],
                            'route' => [
                                'name' => 'hr.employees.index',

                            ]
                        ],
                        [
                            'label' => __('calendar'),
                            'icon'  => ['fal', 'fa-calendar'],
                            'route' => [
                                'name' => 'hr.calendars.index',

                            ]
                        ],
                        [
                            'label' => __('time sheets'),
                            'icon'  => ['fal', 'fa-stopwatch'],
                            'route' => [
                                'name' => 'hr.time-sheets.index',

                            ]
                        ],
                        [
                            'label' => __('working place'),
                            'icon'  => ['fal', 'fa-building'],
                            'route' => [
                                'name' => 'hr.working-places.index',

                            ]
                        ]
                    ]
                ]
            ];
        }

        if ($user->can('sysadmin.view')) {
            $navigation['sysadmin'] = [
                'name'    => __('sysadmin'),
                'icon'    => ['fal', 'fa-users-cog'],
                'route'   => 'sysadmin.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('users'),
                            'icon'  => ['fal', 'fa-terminal'],
                            'route' => [
                                'name' => 'sysadmin.users.index',

                            ]
                        ],
                        [
                            'label' => __('guests'),
                            'icon'  => ['fal', 'fa-user-alien'],
                            'route' => [
                                'name' => 'sysadmin.guests.index',

                            ]
                        ],
                        [
                            'label' => __('system settings'),
                            'icon'  => ['fal', 'fa-cog'],
                            'route' => [
                                'name' => 'sysadmin.settings',

                            ]
                        ],
                    ]
                ]
            ];
        }


        $secondaryNavigation = [];


        if ($user->can('fulfilment.view')) {
            $secondaryNavigation['fulfilment'] = [
                'name'  => __('fulfilment'),
                'icon'  => ['fal', 'fa-dolly-empty'],
                'route' => 'fulfilment.dashboard'
            ];
        }

        if ($user->can('shops.view')) {
            $secondaryNavigation['dropshipping'] = [
                'name'  => __('dropshipping'),
                'icon'  => ['fal', 'fa-parachute-box'],
                'route' => 'dropshipping.dashboard'
            ];
        }


        return [
            'navigation'           => $navigation,
            'secondaryNavigation'  => $secondaryNavigation,
            'shopsInDropDown'      => ShopsNavigationResource::collection(Shop::all()),
            'warehousesInDropDown' => WarehousesNavigationResource::collection(Warehouse::all()),

        ];
    }
}
