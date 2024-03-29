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

        if ($user->hasPermissionTo("fulfilment.$fulfilment->id.view")) {


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
                            'label'   => __('products'),
                            'tooltip' => __('Products'),
                            'icon'    => ['fal', 'fa-cube'],
                            'root'    => 'grp.org.fulfilments.show.operations.products.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.operations.pallets.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
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
                            'label'   => __('Proformas'),
                            'tooltip' => __('Proforma invoices'),
                            'icon'    => ['fal', 'fa-receipt'],
                            'root'    => 'grp.org.fulfilments.show.operations.fulfilment_proformas.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.operations.pallets.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                        [
                            'label'   => __('invoices'),
                            'tooltip' => __('Invoices'),
                            'icon'    => ['fal', 'fa-file-invoice-dollar'],
                            'root'    => 'grp.org.fulfilments.show.operations.invoiced.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.operations.invoices.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],

                    ]
                ]

            ];

            $navigation['web'] = [
                'root'    => 'grp.org.fulfilments.show.web.websites.',
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
                    'subSections' => [

                        $fulfilment->shop->website ?
                        [
                            'label'   => __('webpages'),
                            'tooltip' => __('Webpages'),
                            'icon'    => ['fal', 'fa-browser'],
                            'root'    => 'grp.org.fulfilments.show.web.websites.show.webpages.',
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.web.websites.show.webpages.index',
                                'parameters' => [
                                    $fulfilment->organisation->slug,
                                    $fulfilment->slug,
                                    $fulfilment->shop->website->slug
                                ]

                            ]
                        ] : null,
                    ],
                ]


            ];

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
        }

        return $navigation;
    }
}
