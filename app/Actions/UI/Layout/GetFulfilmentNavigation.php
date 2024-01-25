<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 15:37:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetFulfilmentNavigation
{
    use AsAction;

    public function handle(Fulfilment $fulfilment, User $user): array
    {
        $navigation = [];


        if ($user->hasPermissionTo("fulfilment.$fulfilment->id.stored-items.view")) {
            $navigation['fulfilment'] = [
                'icon'  => ['fal', 'fa-pallet-alt'],
                'label' => __('Stored items'),
                'route' => [
                    'name'       => 'grp.org.fulfilment.shops.show',
                    'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                ],


                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('crm'),
                            'icon'  => ['fal', 'fa-users'],
                            'route' => [
                                'name'       => 'grp.org.fulfilment.shops.crm',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug],

                            ]
                        ],


                    ]


                ],
            ];
        }

        if ($user->hasPermissionTo("fulfilment.$fulfilment->id.view")) {
            $navigation['web'] = [
                'scope' => 'websites',
                'icon'  => ['fal', 'fa-globe'],
                'label' => __('Website'),
                'route' =>

                    $fulfilment->shop->website
                        ?
                        [
                            'name'       => 'grp.org.fulfilment.shops.show.websites.show',
                            'parameters' => [
                                $fulfilment->organisation->slug,
                                $fulfilment->slug,
                                $fulfilment->shop->website->slug
                            ]

                        ]
                        :
                        [
                            'name'       => 'grp.org.fulfilment.shops.show.websites.index',
                            'parameters' => [
                                $fulfilment->organisation->slug,
                                $fulfilment->slug,
                            ]

                        ]


                ,


                'topMenu' => [
                    'subSections' => [
                        [
                            'icon'  => ['fal', 'fa-chart-network'],
                            'route' => [
                                'name' => 'grp.org.fulfilment.shops.web.dashboard',
                            ]
                        ],

                        [
                            'tooltip' => __('websites'),
                            'icon'    => ['fal', 'fa-globe'],
                            'route'   => [
                                'all'      => 'grp.org.fulfilment.shops.web.websites.index',
                                'selected' => 'grp.org.fulfilment.shops.web.websites.show',

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
                                'all'      => ['grp.org.fulfilment.shops.web.webpages.index'],
                                'selected' => ['grp.org.fulfilment.shops.web.websites.show.webpages.index'],

                            ]
                        ],
                    ],
                ]


            ];

            $navigation['crm'] = [
                'scope' => 'shops',
                'label' => __('Customers'),
                'icon'  => ['fal', 'fa-user'],

                'route' => [
                    'name'       => 'grp.org.fulfilment.shops.crm.dashboard',
                    'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                ],

                'topMenu' => [
                    'subSections' => [

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

            ];
        }

        return $navigation;
    }
}
