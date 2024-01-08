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

        $navigation['shops_navigation']=[];
        foreach ($organisation->authorisedModels()->where('user_id', $user->id)->where('model_type', 'Shop')->get() as $authorisedModel) {
            $shop                                        =$authorisedModel->model;
            $navigation['shops_navigation'][$shop->slug] = GetShopNavigation::run($shop, $user);
        }

        $navigation['warehouses_navigation']=[];
        foreach ($organisation->authorisedModels()->where('user_id', $user->id)->where('model_type', 'Warehouse')->get() as $authorisedModel) {
            $warehouse                                             =$authorisedModel->model;
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

        if ($user->hasPermissionTo("production.$organisation->slug.view")) {
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

        if ($user->hasPermissionTo("procurement.$organisation->slug.view")) {
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

        if ($user->hasPermissionTo("accounting.$organisation->slug.view")) {
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

        if ($user->hasPermissionTo("human-resources.$organisation->slug.view")) {
            $navigation['hr'] = [
                'label'   => __('human resources'),
                'icon'    => ['fal', 'fa-user-hard-hat'],
                'route'   => 'grp.org.hr.dashboard',
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('job positions'),
                            'icon'  => ['fal', 'fa-network-wired'],
                            'route' => [
                                'name' => 'grp.org.hr.job-positions.index',

                            ]
                        ],
                        [
                            'label' => __('employees'),
                            'icon'  => ['fal', 'fa-user-hard-hat'],
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
                            'label' => __('working place'),
                            'icon'  => ['fal', 'fa-building'],
                            'route' => [
                                'name' => 'grp.org.hr.workplaces.index',

                            ]
                        ]
                    ]
                ]
            ];
        }


        return $navigation;
    }
}
