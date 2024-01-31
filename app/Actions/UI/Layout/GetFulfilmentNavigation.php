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

        if ($user->hasPermissionTo("fulfilment.$fulfilment->id.view")) {


            $navigation['fulfilment'] = [

                'label' => __('Fulfilment'),
                'icon'  => ['fal', 'fa-hand-holding-box'],

                'route' => [
                    'name'       => 'grp.org.fulfilments.show.catalogue.dashboard',
                    'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                ],

                'topMenu' => [
                    'subSections' => [


                    ]
                ]

            ];

            $navigation['web'] = [
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


                        [
                            'label'   => __('webpages'),
                            'tooltip' => __('Webpages'),
                            'icon'    => ['fal', 'fa-browser'],
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.web.websites.index',
                                'parameters' => [
                                    $fulfilment->organisation->slug,
                                    $fulfilment->slug,
                                ]

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
                    'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                    'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                ],

                'topMenu' => [
                    'subSections' => [

                        [
                            'label'   => __('customers'),
                            'tooltip' => __('Customers'),
                            'icon'    => ['fal', 'fa-user'],
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                                'parameters' => [$fulfilment->organisation->slug, $fulfilment->slug]
                            ],
                        ],
                        [
                            'label'   => __('prospects'),
                            'tooltip' => __('Prospects'),
                            'icon'    => ['fal', 'fa-user-plus'],
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
