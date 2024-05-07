<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Layout;

use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetOrganisationNavigation
{
    use AsAction;

    public function handle(User $user, Organisation $organisation): array
    {
        $navigation = [];


        if ($user->hasPermissionTo("shops.$organisation->id.view")) {
            $navigation['shops_index'] = [
                'label'   => __('Shops'),
                'scope'   => 'shops',
                'icon'    => ['fal', 'fa-store-alt'],
                'root'    => 'grp.org.shops.index',
                'route'   => [
                    'name'       => 'grp.org.shops.index',
                    'parameters' => [$organisation->slug],
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label'   => __('dashboard'),
                            'tooltip' => __('Dashboard'),
                        ]
                    ]
                ]

            ];
        }

        $navigation['shops_navigation'] = [];
        foreach (
            $organisation->authorisedModels()->where('user_id', $user->id)->where('model_type', 'Shop')->get() as $authorisedModel
        ) {
            $shop                                        = $authorisedModel->model;
            $navigation['shops_navigation'][$shop->slug] = GetShopNavigation::run($shop, $user);
        }


        if ($user->hasPermissionTo("fulfilments.$organisation->id.view")) {
            $navigation['fulfilments_index'] = [
                'label'   => __('Fulfilment shops'),
                'root'    => 'grp.org.fulfilments.index',
                'icon'    => ['fal', 'fa-store-alt'],
                'route'   => [
                    'name'       => 'grp.org.fulfilments.index',
                    'parameters' => [$organisation->slug],
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label'   => __('dashboard'),
                            'tooltip' => __('Dashboard'),
                        ]
                    ]
                ]
            ];
        }


        $navigation['fulfilments_navigation'] = [];
        foreach (
            $organisation->authorisedModels()->where('user_id', $user->id)->where('model_type', 'Fulfilment')->get() as $authorisedModel
        ) {
            $fulfilment         = $authorisedModel->model;
            $navigation['fulfilments_navigation']
            [$fulfilment->slug] = GetFulfilmentNavigation::run($fulfilment, $user);
        }

        if ($user->hasPermissionTo("dispatching.$organisation->id.view")) {
            $navigation["dispatching"] = [
                "root"    => "grp.org.dispatch.",
                "label"   => __("dispatching"),
                "icon"    => ["fal", "fa-conveyor-belt-alt"],
                "route"   => [
                    "name"       => "grp.org.dispatch.backlog",
                    "parameters" => [$organisation->slug],
                ],
                "topMenu" => [
                    'subSections' => [
                        [
                            'icon'  => ['fal', 'fa-tasks-alt'],
                            'route' => [
                                "name"       => "grp.org.dispatch.backlog",
                                "parameters" => [$organisation->slug],
                            ]
                        ],
                        [
                            'label' => __('delivery notes'),
                            'icon'  => ['fal', 'fa-truck'],
                            'route' => [
                                "name"       => "grp.org.dispatch.delivery-notes",
                                "parameters" => [$organisation->slug],
                            ]
                        ],
                    ]
                ],
            ];
        }


        if ($user->hasPermissionTo("inventory.$organisation->id.view")) {
            $navigation["inventory"] = [
                "root"    => "grp.org.inventory.",
                "label"   => __("inventory"),
                "icon"    => ["fal", "fa-pallet-alt"],
                "route"   => [
                    "name"       => "grp.org.inventory.dashboard",
                    "parameters" => [$organisation->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "icon"  => ["fal", "fa-chart-network"],
                            "route" => [
                                "name"       => "grp.org.inventory.dashboard",
                                "parameters" => [$organisation->slug],
                            ],
                        ],
                        [
                            "label" => __("SKUs"),
                            "icon"  => ["fal", "fa-box"],
                            "route" => [
                                "name"       => "grp.org.inventory.org-stocks.index",
                                "parameters" => [$organisation->slug],
                            ],
                        ],
                        [
                            "label"   => __("SKUs Families"),
                            "tooltip" => __("SKUs families"),
                            "icon"    => ["fal", "fa-boxes-alt"],
                            "route"   => [
                                "name"       => "grp.org.inventory.org-stock-families.index",
                                "parameters" => [$organisation->slug],
                            ],
                        ],
                    ],
                ],
            ];
        }


        if ($user->hasPermissionTo("warehouses.$organisation->id.view")) {
            $navigation['warehouses_index'] = [
                'label'   => __('Warehouses'),
                'scope'   => 'warehouses',
                'icon'    => ['fal', 'fa-warehouse-alt'],
                'root'    => 'grp.org.warehouses.index',
                'route'   => [
                    'name'       => 'grp.org.warehouses.index',
                    'parameters' => [$organisation->slug],
                ],
                'topMenu' => [
                    'links' => [
                        [
                            'label'   => __('dashboard'),
                            'tooltip' => __('Dashboard'),
                        ]
                    ]
                ]
            ];
        }


        $navigation['warehouses_navigation'] = [];
        foreach (
            $organisation->authorisedModels()->where('user_id', $user->id)
                ->where('model_type', 'Warehouse')
                ->get() as $authorisedModel
        ) {
            $warehouse                                             = $authorisedModel->model;
            $navigation['warehouses_navigation'][$warehouse->slug] = GetWarehouseNavigation::run($warehouse, $user);
        }


        if ($user->hasPermissionTo('fulfilment.view')
            //  and app('currentTenant')->marketStats->number_shops_type_fulfilment
        ) {
            $navigation['fulfilment'] = [
                'label'   => __('fulfilment'),
                'icon'    => ['fal', 'fa-dolly-flatbed-alt'],
                'route'   => 'grp.fulfilment.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'icon'  => ['fal', 'fa-chart-network'],
                            'root'  => 'grp.fulfilment.dashboard',
                            'route' => [
                                'name' => 'grp.fulfilment.dashboard',
                            ]
                        ],

                        [
                            'label' => __('customers'),
                            'icon'  => ['fal', 'fa-user-tie'],
                            'root'  => 'grp.fulfilment.customers.',
                            'route' => [
                                'name' => 'grp.fulfilment.customers.index',
                            ]
                        ],
                        [
                            'label'   => __('stored items'),
                            'tooltip' => __('stored items'),
                            'icon'    => ['fal', 'fa-narwhal'],
                            'root'    => 'grp.fulfilment.stored-items.',
                            'route'   => [
                                'name' => 'grp.fulfilment.stored-items.index',
                            ]
                        ],
                        [
                            'label'   => __('orders'),
                            'tooltip' => __('orders'),
                            'icon'    => ['fal', 'fa-business-time'],
                            'root'    => 'grp.fulfilment.orders.',
                            'route'   => [
                                'name' => 'grp.fulfilment.orders.index',
                            ]
                        ],

                    ],


                ]
            ];
        }


        if ($user->hasPermissionTo("productions.$organisation->id.view")) {
            $navigation['productions_index'] = [
                'label'   => __('Productions'),
                'scope'   => 'productions',
                'icon'    => ['fal', 'fa-industry'],
                'root'    => 'grp.org.manufacturing.productions.index',
                'route'   => [
                    'name'       => 'grp.org.manufacturing.productions.index',
                    'parameters' => [$organisation->slug],
                ],
                'topMenu' => [
                    'links' => [
                        [
                            'label'   => __('dashboard'),
                            'tooltip' => __('Dashboard'),
                        ]
                    ]
                ]
            ];
        }

        $navigation['productions_navigation'] = [];
        foreach (
            $organisation->authorisedModels()->where('user_id', $user->id)
                ->where('model_type', 'Production')
                ->get() as $authorisedModel
        ) {
            $production         = $authorisedModel->model;
            $navigation['productions_navigation']
            [$production->slug] = GetManufacturingNavigation::run($production, $user);
        }

        if ($user->hasPermissionTo("procurement.$organisation->id.view")) {
            $navigation['procurement'] = [
                'root'    => 'grp.org.procurement',
                'label'   => __('procurement'),
                'icon'    => ['fal', 'fa-box-usd'],
                'route'   => [
                    'name'       => 'grp.org.procurement.dashboard',
                    'parameters' => [$organisation->slug],
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('agents'),
                            'icon'  => ['fal', 'fa-people-arrows'],
                            'root'  => 'grp.org.procurement.agents.',
                            'route' => [
                                'name'       => 'grp.org.procurement.agents.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
                            'root'  => 'grp.org.procurement.suppliers.',
                            'route' => [
                                'name'       => 'grp.org.procurement.suppliers.index',
                                'parameters' => [$organisation->slug],
                            ]
                        ],
                        [
                            'label' => __('purchase orders'),
                            'icon'  => ['fal', 'fa-clipboard-list'],
                            'root'  => 'grp.org.procurement.purchase-orders.',
                            'route' => [
                                'name'       => 'grp.org.procurement.purchase-orders.index',
                                'parameters' => [$organisation->slug],
                            ]
                        ],
                    ]
                ]
            ];
        }

        if ($user->hasPermissionTo("accounting.$organisation->id.view")) {
            $navigation['accounting'] = [
                'root'  => 'grp.org.accounting',
                'scope' => 'shops',
                'label' => __('Accounting'),
                'icon'  => ['fal', 'fa-abacus'],
                'route' => [
                    'name'       => 'grp.org.accounting.dashboard',
                    'parameters' => [$organisation->slug],
                ],


                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('Providers'),
                            'icon'  => ['fal', 'fa-cash-register'],
                            'root'  => 'grp.org.accounting.org-payment-service-providers.',
                            'route' => [
                                'name'       => 'grp.org.accounting.org-payment-service-providers.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('Accounts'),
                            'icon'  => ['fal', 'fa-money-check-alt'],
                            'root'  => 'grp.org.accounting.payment-accounts.',
                            'route' => [
                                'name'       => 'grp.org.accounting.payment-accounts.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('Payments'),
                            'icon'  => ['fal', 'fa-coins'],
                            'root'  => 'grp.org.accounting.payments.',
                            'route' => [
                                'name'       => 'grp.org.accounting.payments.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('Invoices'),
                            'icon'  => ['fal', 'fa-file-invoice-dollar'],
                            'root'  => 'grp.org.accounting.invoices.',
                            'route' => [
                                'name'       => 'grp.org.accounting.invoices.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],

                    ],
                ]
            ];
        }

        if ($user->hasPermissionTo("human-resources.$organisation->id.view")) {
            $navigation['hr'] = [
                'label'   => __('human resources'),
                'icon'    => ['fal', 'fa-user-hard-hat'],
                'root'    => 'grp.org.hr',
                'route'   => [
                    'name'       => 'grp.org.hr.dashboard',
                    'parameters' => [$organisation->slug],
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('job positions'),
                            'icon'  => ['fal', 'fa-network-wired'],
                            'root'  => 'grp.org.hr.job-positions.',
                            'route' => [
                                'name'       => 'grp.org.hr.job-positions.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('employees'),
                            'icon'  => ['fal', 'fa-user-hard-hat'],
                            'root'  => 'grp.org.hr.employees.',
                            'route' => [
                                'name'       => 'grp.org.hr.employees.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('calendar'),
                            'icon'  => ['fal', 'fa-calendar'],
                            'root'  => 'grp.org.hr.calendars.',
                            'route' => [
                                'name'       => 'grp.org.hr.calendars.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('time sheets'),
                            'icon'  => ['fal', 'fa-stopwatch'],
                            'root'  => 'grp.org.hr.time-sheets.',
                            'route' => [
                                'name'       => 'grp.org.hr.time-sheets.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('working place'),
                            'icon'  => ['fal', 'fa-building'],
                            'root'  => 'grp.org.hr.workplaces.',
                            'route' => [
                                'name'       => 'grp.org.hr.workplaces.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ]
                    ]
                ]
            ];
        }


        if ($user->hasPermissionTo('org-reports.'.$organisation->id)) {
            $navigation['reports'] = [
                'label'   => __('Reports'),
                'tooltip' => __('Reports'),
                'icon'    => ['fal', 'fa-chart-line'],
                'root'    => 'grp.org.reports',

                'route' => [
                    'name'       => 'grp.org.reports.index',
                    'parameters' => [$organisation->slug],
                ],

                'topMenu' => [

                    'subSections' => [

                    ]
                ],
            ];
        }


        return $navigation;
    }
}
