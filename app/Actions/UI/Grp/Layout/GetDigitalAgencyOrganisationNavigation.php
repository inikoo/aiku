<?php

/*
 * author Arya Permana - Kirin
 * created on 17-10-2024-10h-27m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\UI\Grp\Layout;

use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\OrganisationAuthorisedModels;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDigitalAgencyOrganisationNavigation
{
    use AsAction;

    public function handle(User $user, Organisation $organisation): array
    {
        $navigation = [];

        if ($user->hasAnyPermission(
            [
                'org-supervisor.'.$organisation->id,
                'shops-view.'.$organisation->id
            ]
        )) {

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

        $shops_navigation = [];
        /** @var OrganisationAuthorisedModels $authorisedModel */
        foreach (
            $organisation->authorisedModels()->where('user_id', $user->id)->where('model_type', 'Shop')->get() as $authorisedModel
        ) {
            /** @var Shop $shop */
            $shop                          = $authorisedModel->model;
            $shops_navigation[$shop->slug] = [
                'type'          => $shop->type,
                'subNavigation' => GetShopNavigation::run($shop, $user)
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
                            'icon'  => ['fal', 'fa-chart-network'],
                            'root'  => 'grp.org.accounting.dashboard',
                            'route' => [
                                'name'       => 'grp.org.accounting.dashboard',
                                'parameters' => [$organisation->slug],
                            ]
                        ],
                        /*
                        [
                            'label' => __('Providers'),
                            'icon'  => ['fal', 'fa-cash-register'],
                            'root'  => 'grp.org.accounting.org-payment-service-providers.',
                            'route' => [
                                'name'       => 'grp.org.accounting.org-payment-service-providers.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        */
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
                        [
                            'label' => __('Customers Balances'),
                            'icon'  => ['fal', 'fa-piggy-bank'],
                            'root'  => 'grp.org.accounting.balances.',
                            'route' => [
                                'name'       => 'grp.org.accounting.balances.index',
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
                            "tooltip" => __("Dashboard"),
                            "icon"    => ["fal", "fa-chart-network"],
                            "root"    => "grp.org.hr.dashboard",
                            "route"   => [
                                "name"       => "grp.org.hr.dashboard",
                                'parameters' => [$organisation->slug],
                            ],
                        ],

                        [
                            'tooltip' => __('working place'),
                            'icon'    => ['fal', 'fa-building'],
                            'root'    => 'grp.org.hr.workplaces.',
                            'route'   => [
                                'name'       => 'grp.org.hr.workplaces.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],


                        [
                            'tooltip' => __('Responsibilities'),
                            'icon'    => ['fal', 'fa-clipboard-list-check'],
                            'root'    => 'grp.org.hr.job_positions.',
                            'route'   => [
                                'name'       => 'grp.org.hr.job_positions.index',
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
                        // [
                        //     'label' => __('calendar'),
                        //     'icon'  => ['fal', 'fa-calendar'],
                        //     'root'  => 'grp.org.hr.calendars.',
                        //     'route' => [
                        //         'name'       => 'grp.org.hr.calendars.index',
                        //         'parameters' => [$organisation->slug],

                        //     ]
                        // ],

                        [
                            'label' => __('clocking machines'),
                            'icon'  => ['fal', 'fa-chess-clock'],
                            'root'  => 'grp.org.hr.clocking_machines.',
                            'route' => [
                                'name'       => 'grp.org.hr.clocking_machines.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],
                        [
                            'label' => __('timesheets'),
                            'icon'  => ['fal', 'fa-stopwatch'],
                            'root'  => 'grp.org.hr.timesheets.',
                            'route' => [
                                'name'       => 'grp.org.hr.timesheets.index',
                                'parameters' => [$organisation->slug],

                            ]
                        ],


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

        if ($user->hasPermissionTo('org-supervisor.'.$organisation->id)) {
            $navigation['setting'] = [
                "root"    => "grp.org.settings.",
                "icon"    => ["fal", "fa-sliders-h"],
                "label"   => __("Settings"),
                "route"   => [
                    "name"       => 'grp.org.settings.edit',
                    "parameters" => [$organisation->slug],
                ],
                "topMenu" => [
                    "subSections" => [

                    ],
                ],
            ];
        }

        return $navigation;
    }
}
