<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Feb 2024 22:34:23 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\UI\Layout;

use App\Models\CRM\WebUser;
use Lorisleiva\Actions\Concerns\AsAction;

class GetRetinaFulfilmentNavigation
{
    use AsAction;

    public function handle(WebUser $webUser): array
    {
        $groupNavigation = [];


        $groupNavigation['home'] = [
            'label'   => __('Home'),
            'icon'    => ['fal', 'fa-home'],
            'root'    => 'retina.dashboard.show',
            'route'   => [
                'name' => 'retina.dashboard.show'
            ],
            'topMenu' => [


            ]

        ];


        $additionalSubsections = [];

        if ($webUser?->customer?->fulfilmentCustomer?->number_pallets_status_storing) {
            $additionalSubsections = [
                [
                    'label' => __('returns'),
                    'icon'  => ['fal', 'fa-truck-ramp'],
                    'root'  => 'retina.fulfilment.storage.pallet-returns.',
                    'route' => [
                        'name' => 'retina.fulfilment.storage.pallet-returns.index'
                    ]
                ]
            ];
        }



        $groupNavigation['storage'] = [
            'label'   => __('Storage'),
            'icon'    => ['fal', 'fa-pallet'],
            'root'    => 'retina.fulfilment.storage.',
            'route'   => [
                'name' => 'retina.fulfilment.storage.dashboard'
            ],
            'topMenu' => [
                'subSections' => [
                    [
                        'label' => __('pallets'),
                        'icon'  => ['fal', 'fa-pallet'],
                        'root'  => 'retina.fulfilment.storage.pallets.',
                        'route' => [
                            'name' => 'retina.fulfilment.storage.pallets.index'
                        ]
                    ],
                    [
                        'label' => __('deliveries'),
                        'icon'  => ['fal', 'fa-truck'],
                        'root'  => 'retina.fulfilment.storage.pallet-deliveries.',
                        'route' => [
                            'name' => 'retina.fulfilment.storage.pallet-deliveries.index'
                        ]
                    ],
                    ...$additionalSubsections,


                ]
            ]
        ];

        if ($webUser->customer->fulfilmentCustomer->items_storage) {
            $groupNavigation['stored_items'] = [
                'label'   => __('Skus'),
                'icon'    => ['fal', 'fa-barcode'],
                'root'    => 'retina.fulfilment.itemised_storage.',
                'route'   => [
                    'name' => 'retina.fulfilment.itemised_storage.stored_items.index'
                ],
                'topMenu' => [
                    'subSections' => [

                        [
                            'label' => __('SKUs'),
                            'icon'  => ['fal', 'fa-barcode'],
                            'root'  => 'retina.fulfilment.itemised_storage.stored_items.',
                            'route' => [
                                'name' => 'retina.fulfilment.itemised_storage.stored_items.index'
                            ]
                        ],
                        [
                            'label' => __('Audits'),
                            'icon'  => ['fal', 'fa-ballot-check'],
                            'root'  => 'retina.fulfilment.itemised_storage.stored_items_audits.index',
                            'route' => [
                                'name' => 'retina.fulfilment.itemised_storage.stored_items_audits.index'
                            ]
                        ]

                    ]
                ]
            ];
        }

        $groupNavigation['pricing'] = [
            'label'   => __('Pricing'),
            'icon'    => ['fal', 'fa-usd-circle'],
            'root'    => 'retina.fulfilment.pricing',
            'route'   => [
                'name' => 'retina.fulfilment.pricing'
            ],
            'topMenu' => [


                [
                    'label' => __('Pricing'),
                    'icon'  => ['fal', 'fa-usd-circle'],
                    'root'  => 'retina.fulfilment.pricing',
                    'route' => [
                        'name' => 'retina.fulfilment.storage.pricing'
                    ]
                ],
            ]

        ];


        /*$groupNavigation['dropshipping'] = [
            'label'   => __('Dropshipping'),
            'icon'    => ['fal', 'fa-hand-holding-box'],
            'route'   => [
                'name' => 'retina.sysadmin.dashboard'
            ],
            'topMenu' => [
                'subSections' => [
                    [
                        'label' => __('users'),
                        'icon'  => ['fal', 'fa-terminal'],
                        'root'  => 'retina.sysadmin.users.',
                        'route' => [
                            'name' => 'retina.sysadmin.web-users.index',

                        ]
                    ],

                    [
                        'label' => __('system settings'),
                        'icon'  => ['fal', 'fa-cog'],
                        'root'  => 'retina.sysadmin.settings.',
                        'route' => [
                            'name' => 'retina.sysadmin.settings.edit',

                        ]
                    ],
                ]
            ]
        ];*/

        $groupNavigation['billing'] = [
            'label'   => __('billing'),
            'icon'    => ['fal', 'fa-file-invoice-dollar'],
            'root'    => 'retina.fulfilment.billing.',
            'route'   => [
                'name' => 'retina.fulfilment.billing.dashboard'
            ],
            'topMenu' => [
                'subSections' => [
                    [
                        'label' => __('next bill'),
                        'icon'  => ['fal', 'fa-receipt'],
                        'root'  => 'retina.fulfilment.billing.next_recurring_bill',
                        'route' => [
                            'name' => 'retina.fulfilment.billing.next_recurring_bill',

                        ]
                    ],

                    [
                        'label' => __('invoices'),
                        'icon'  => ['fal', 'fa-file-invoice-dollar'],
                        'root'  => 'retina.fulfilment.billing.invoices.',
                        'route' => [
                            'name' => 'retina.fulfilment.billing.invoices.index',

                        ]
                    ],
                ]
            ]
        ];


        if ($webUser->is_root) {
            $groupNavigation['sysadmin'] = [
                'label'   => __('manage account'),
                'icon'    => ['fal', 'fa-users-cog'],
                'root'    => 'retina.sysadmin.',
                'route'   => [
                    'name' => 'retina.sysadmin.dashboard'
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('users'),
                            'icon'  => ['fal', 'fa-user-circle'],
                            'root'  => 'retina.sysadmin.web-users.',
                            'route' => [
                                'name' => 'retina.sysadmin.web-users.index',

                            ]
                        ],

                        [
                            'label' => __('account settings'),
                            'icon'  => ['fal', 'fa-cog'],
                            'root'  => 'retina.sysadmin.settings.',
                            'route' => [
                                'name' => 'retina.sysadmin.settings.edit',

                            ]
                        ],
                    ]
                ]
            ];
            /*            $groupNavigation['dropshipping'] = [
                            'label'   => __('Dropshipping'),
                            'icon'    => ['fal', 'fa-parachute-box'],
                            'root'    => 'retina.dropshipping.',
                            'route'   => [
                                'name' => 'retina.dropshipping.dashboard'
                            ],
                            'topMenu' => [
                                'subSections' => [
                                    [
                                        'label' => __('Products'),
                                        'icon'  => ['fal', 'fa-cube'],
                                        'root'  => 'retina.dropshipping.products.',
                                        'route' => [
                                            'name' => 'retina.dropshipping.products.index',

                                        ]
                                    ],
                                ]
                            ]
                        ];*/
        }


        return $groupNavigation;
    }
}
