<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Layout;

use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetFulfilmentNavigation
{
    use AsAction;

    public function handle(Fulfilment $fulfilment, User $user): array
    {
        $navigation = [];

        if ($user->hasPermissionTo("fulfilment-shop.$fulfilment->id.view")) {
            $navigation['dashboard'] = [
                'root'  => 'grp.org.fulfilments.show.dashboard',
                'label' => __('Dashboard'),
                'icon'  => ['fal', 'fa-chart-line'],

                'route' => [
                    'name'       => 'grp.org.fulfilments.show.dashboard',
                    'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                ],

                'topMenu' => [
                    'subSections' => []
                ]

            ];
            $navigation['assets'] = [
                'root'  => 'grp.org.fulfilments.show.assets.',
                'label' => __('Billables'),
                'icon'  => ['fal', 'fa-ballot'],

                'route' => [
                    'name'       => 'grp.org.fulfilments.show.assets.index',
                    'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                ],

                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('rentals'),
                            'icon'  => ['fal', 'fa-garage'],
                            'root'  => 'grp.org.fulfilments.show.assets.rentals.',
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.assets.rentals.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                        [
                            'label' => __('services'),
                            'icon'  => ['fal', 'fa-concierge-bell'],
                            'root'  => 'grp.org.fulfilments.show.assets.services.',
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.assets.services.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                        [
                            'label' => __('goods'),
                            'icon'  => ['fal', 'fa-cube'],
                            'root'  => 'grp.org.fulfilments.show.assets.outers.',
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.assets.outers.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                        [
                            "label"   => __("Shipping"),
                            "tooltip" => __("Shipping"),
                            "icon"    => ["fal", "fa-shipping-fast"],
                            'root'    => 'grp.org.fulfilments.show.assets.outers.',
                            "route"   => [
                                'name'       => 'grp.org.fulfilments.show.assets.shipping.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],

                    ]
                ]

            ];

            $navigation['operations'] = [
                'root'  => 'grp.org.fulfilments.show.operations.',
                'label' => __('Operations'),
                'icon'  => ['fal', 'fa-route'],

                'route' => [
                    'name'       => 'grp.org.fulfilments.show.operations.dashboard',
                    'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                ],

                'topMenu' => [
                    'subSections' => [
                        [
                            "tooltip" => __("Dashboard"),
                            "icon"    => ["fal", "fa-chart-network"],
                            "root"    => "grp.org.fulfilments.show.operations.dashboard",
                            "route"   => [
                                "name"       => "grp.org.fulfilments.show.operations.dashboard",
                                "parameters" => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],

                        [
                            'label'   => __('pallets'),
                            'tooltip' => __('Pallets'),
                            'icon'    => ['fal', 'fa-pallet'],
                            'root'    => 'grp.org.fulfilments.show.operations.pallets.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.operations.pallets.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],

                        [
                            'label'   => __('deliveries'),
                            'tooltip' => __('Deliveries'),
                            'icon'    => ['fal', 'fa-truck-couch'],
                            'root'    => 'grp.org.fulfilments.show.operations.pallet-deliveries.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.operations.pallet-deliveries.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                        [
                            'label'   => __('returns'),
                            'tooltip' => __('Returns'),
                            'icon'    => ['fal', 'fa-sign-out'],
                            'root'    => 'grp.org.fulfilments.show.operations.pallet-returns.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.operations.pallet-returns.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                        [
                            'label'   => __('Recurring bills'),
                            'tooltip' => __('Recurring bills'),
                            'icon'    => ['fal', 'fa-receipt'],
                            'root'    => 'grp.org.fulfilments.show.operations.recurring_bills.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.operations.recurring_bills.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                        [
                            'label'   => __('invoices'),
                            'tooltip' => __('Invoices'),
                            'icon'    => ['fal', 'fa-file-invoice-dollar'],
                            'root'    => 'grp.org.fulfilments.show.operations.invoices.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.operations.invoices.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],

                    ]
                ]

            ];
            if ($fulfilment->shop->website) {
                $navigation['web'] = [
                    'root'    => 'grp.org.fulfilments.show.web.',
                    'scope'   => 'websites',
                    'icon'    => ['fal', 'fa-globe'],
                    'label'   => __('Website'),
                    'route'   =>

                        $fulfilment->shop->website
                            ?
                            [
                                'name'       => 'grp.org.fulfilments.show.web.websites.show',
                                'parameters' => [
                                    $fulfilment->organisation->slug,
                                    $fulfilment->slug,
                                    $fulfilment->shop->website->slug
                                ]

                            ]
                            :
                            [
                                'name'       => 'grp.org.fulfilments.show.web.websites.index',
                                'parameters' => [
                                    $fulfilment->organisation->slug,
                                    $fulfilment->slug,
                                ]

                            ],
                    'topMenu' => [
                        'subSections' =>

                            [
                                [
                                    "label"   => __("Website"),
                                    "tooltip" => __("website"),
                                    "icon"    => ["fal", "fa-globe"],
                                    'root'    => 'grp.org.fulfilments.show.web.websites.',
                                    "route"   => [
                                        "name"       => "grp.org.fulfilments.show.web.websites.show",
                                        "parameters" => [
                                            $fulfilment->organisation->slug,
                                            $fulfilment->slug,
                                            $fulfilment->shop->website->slug
                                        ],
                                    ],
                                ],


                                [
                                    'label'   => __('webpages'),
                                    'tooltip' => __('Webpages'),
                                    'icon'    => ['fal', 'fa-browser'],
                                    'root'    => 'grp.org.fulfilments.show.web.webpages.',
                                    'route'   => [
                                        'name'       => 'grp.org.fulfilments.show.web.webpages.index',
                                        'parameters' => [
                                            $fulfilment->organisation->slug,
                                            $fulfilment->slug,
                                            $fulfilment->shop->website->slug
                                        ]

                                    ]
                                ]
                            ]
                    ],


                ];
            }
            $navigation['crm'] = [
                'scope' => 'shops',
                'label' => __('Customers'),
                'icon'  => ['fal', 'fa-user'],
                'root'  => 'grp.org.fulfilments.show.crm.',
                'route' => [
                    'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                    'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                ],

                'topMenu' => [
                    'subSections' => [
                        [
                            'label'   => __('customers'),
                            'tooltip' => __('Customers'),
                            'icon'    => ['fal', 'fa-user'],
                            'root'    => 'grp.org.fulfilments.show.crm.customers.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                        [
                            'label'   => __('prospects'),
                            'tooltip' => __('Prospects'),
                            'icon'    => ['fal', 'fa-user-plus'],
                            'root'    => 'grp.org.fulfilments.show.crm.prospects.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.crm.prospects.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                    ]
                ]

            ];
            $navigation['setting'] = [
                "root"  => "grp.org.fulfilments.show.setting.",
                "icon"  => ["fal", "fa-sliders-h"], //TODO: Need icon for this
                "label" => __("Setting"),
                "route" => [
                    "name"       => 'grp.org.fulfilments.show.setting.dashboard',
                    "parameters" => [$fulfilment->organisation->slug, $fulfilment->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "tooltip" => __("mail dashboard"),
                            "icon"    => ["fal", "fa-chart-network"],
                            'root'    => 'grp.org.fulfilments.show.mail.dashboard',
                            "route"   => [
                                "name"       => 'grp.org.fulfilments.show.mail.dashboard',
                                "parameters" => [$fulfilment->organisation->slug, $fulfilment->slug],
                            ],
                        ],
                        [
                            "label"   => __("outboxes"),
                            "tooltip" => __("outboxes"),
                            "icon"    => ["fal", "fa-comment-dollar"],
                            'root'    => 'grp.org.fulfilments.show.mail.outboxes',
                            "route"   => [
                                "name"       => "grp.org.fulfilments.show.mail.outboxes",
                                "parameters" => [$fulfilment->organisation->slug, $fulfilment->slug],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $navigation;
    }
}
