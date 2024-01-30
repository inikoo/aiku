<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 08 Dec 2023 22:08:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetOrganisationNavigation
{
    use AsAction;

    public function handle(User $user, Organisation $organisation): array
    {
        $navigation = [];

        if ($user->hasPermissionTo('org-business-intelligence.'.$organisation->slug)) {
            $navigation['business_intelligence'] = [
                'label' => __('Business Intelligence'),

                'scope' => 'shops',
                'icon'  => ['fal', 'fa-lightbulb'],

                'route' => [
                    'all'      => 'grp.business_intelligence.dashboard',
                    'selected' => 'grp.business_intelligence.shops.show.dashboard'
                ],

                'topMenu' => [

                    'subSections' => [

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
                ],
            ];
        }

        if ($user->hasPermissionTo("shops.$organisation->id.view")) {
            $navigation['shops_index'] = [
                'label' => __('Shops'),
                'scope' => 'shops',
                'icon'  => ['fal', 'fa-store-alt'],

                'route' => [
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
        foreach ($organisation->authorisedModels()->where('user_id', $user->id)->where('model_type', 'Shop')->get() as $authorisedModel) {
            $shop                                        = $authorisedModel->model;
            $navigation['shops_navigation'][$shop->slug] = GetShopNavigation::run($shop, $user);
        }


        if ($user->hasPermissionTo("fulfilments.$organisation->id.view")) {
            $navigation['fulfilments_index'] = [
                'label' => __('Fulfilment shops'),


                'icon' => ['fal', 'fa-store-alt'],

                'route' => [
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
        foreach ($organisation->authorisedModels()->where('user_id', $user->id)->where('model_type', 'Fulfilment')->get() as $authorisedModel) {
            $fulfilment                                              = $authorisedModel->model;
            $navigation['fulfilments_navigation'][$fulfilment->slug] = GetFulfilmentNavigation::run($fulfilment, $user);
        }





        if ($user->hasPermissionTo("warehouses.$organisation->id.view")) {
            $navigation['warehouses_index'] = [
                'label' => __('Warehouses'),

                'scope' => 'warehouses',
                'icon'  => ['fal', 'fa-warehouse-alt'],

                'route' => [
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
        foreach ($organisation->authorisedModels()->where('user_id', $user->id)->where('model_type', 'Warehouse')->get() as $authorisedModel) {
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

        if ($user->hasPermissionTo("production.$organisation->id.view")) {
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
                                'name' => 'grp.org.hr.job-positions.index',

                            ]
                        ],
                        [
                            'label' => __('employees'),
                            'icon'  => ['fal', 'fa-terminal'],
                            'route' => [
                                'name' => 'grp.org.hr.employees.index',

                            ]
                        ],
                        [
                            'label' => __('calendar'),
                            'icon'  => ['fal', 'fa-calendar'],
                            'route' => [
                                'name' => 'grp.org.hr.calendars.index',

                            ]
                        ],
                        [
                            'label' => __('time sheets'),
                            'icon'  => ['fal', 'fa-stopwatch'],
                            'route' => [
                                'name' => 'grp.org.hr.time-sheets.index',

                            ]
                        ],
                        [
                            'label' => __('clocking machines'),
                            'icon'  => ['fal', 'fa-chess-clock'],
                            'route' => [
                                'name' => 'grp.org.hr.clocking-machines.index',

                            ]
                        ]
                    ]
                ]

            ];
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
                            'route' => [
                                'name'       => 'grp.org.procurement.agents.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
                            'route' => [
                                'name'       => 'grp.org.procurement.suppliers.index',
                                'parameters' => [$organisation->slug],
                            ]
                        ],
                        [
                            'label' => __('purchase orders'),
                            'icon'  => ['fal', 'fa-clipboard-list'],
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
                        [
                            'label'   => __('customers'),
                            'tooltip' => __('Customers'),
                            'icon'    => ['fal', 'fa-user'],
                            'route'   => [
                                'all'      => ['grp.org.shops.show.crm.customers.index'],
                                'selected' => ['grp.org.shops.show.crm.customers.index'],

                            ]
                        ],
                        [
                            'label'   => __('prospects'),
                            'tooltip' => __('Prospects'),
                            'icon'    => ['fal', 'fa-user-plus'],
                            'route'   => [
                                'all'      => ['grp.org.shops.show.crm.prospects.index'],
                                'selected' => ['grp.crm.shops.show.prospects.index'],

                            ]
                        ],
                    ],
                ]
            ];
        }

        if ($user->hasPermissionTo("human-resources.$organisation->id.view")) {
            $navigation['hr'] = [
                'root'    => 'grp.org.hr',
                'label'   => __('human resources'),
                'icon'    => ['fal', 'fa-user-hard-hat'],
                'route'   => [
                    'name'       => 'grp.org.hr.dashboard',
                    'parameters' => [$organisation->slug],
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('job positions'),
                            'icon'  => ['fal', 'fa-network-wired'],
                            'route' => [
                                'name'       => 'grp.org.hr.job-positions.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('employees'),
                            'icon'  => ['fal', 'fa-user-hard-hat'],
                            'route' => [
                                'name'       => 'grp.org.hr.employees.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('calendar'),
                            'icon'  => ['fal', 'fa-calendar'],
                            'route' => [
                                'name'       => 'grp.org.hr.calendars.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('time sheets'),
                            'icon'  => ['fal', 'fa-stopwatch'],
                            'route' => [
                                'name'       => 'grp.org.hr.time-sheets.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('working place'),
                            'icon'  => ['fal', 'fa-building'],
                            'route' => [
                                'name'       => 'grp.org.hr.workplaces.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ]
                    ]
                ]
            ];
        }


        return $navigation;
    }
}
