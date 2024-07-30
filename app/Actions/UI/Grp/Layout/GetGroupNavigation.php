<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Layout;

use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetGroupNavigation
{
    use AsAction;

    public function handle(User $user): array
    {
        $groupNavigation = [];

        $groupNavigation['group'] = [
            'label'   => __('Group'),
            'icon'    => ['fal', 'fa-city'],
            'root'    => 'grp.dashboard.show',
            'route'   => [
                'name' => 'grp.dashboard.show'
            ],
            'topMenu' => [
            ]

        ];

        if ($user->hasPermissionTo('goods.view')) {
            $groupNavigation['goods'] = [
                'label'   => __('Goods'),
                'icon'    => ['fal', 'fa-cloud-rainbow'],
                'root'    => 'grp.goods.',
                'route'   => [
                    'name' => 'grp.goods.dashboard'
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('SKUs families'),
                            'icon'  => ['fal', 'fa-boxes-alt'],
                            'root'  => 'grp.goods.stock-families.',
                            'route' => [
                                'name'       => 'grp.goods.stock-families.index',
                                'parameters' => []

                            ]
                        ],
                        [
                            'label' => 'SKUs',
                            'icon'  => ['fal', 'fa-box'],
                            'root'  => 'grp.goods.stocks.',
                            'route' => [
                                'name'       => 'grp.goods.stocks.index',
                                'parameters' => []

                            ]
                        ],

                    ]
                ]

            ];
        }

        if ($user->hasPermissionTo('supply-chain.view')) {
            $groupNavigation['supply-chain'] = [
                'label'   => __('Supply Chain'),
                'icon'    => ['fal', 'fa-box-usd'],
                'root'    => 'grp.supply-chain.',
                'route'   => [
                    'name' => 'grp.supply-chain.dashboard'
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('agents'),
                            'icon'  => ['fal', 'fa-people-arrows'],
                            'root'  => 'grp.supply-chain.agents.',
                            'route' => [
                                'name' => 'grp.supply-chain.agents.index',

                            ]
                        ],
                        [
                            'label' => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
                            'root'  => 'grp.supply-chain.suppliers.',
                            'route' => [
                                'name' => 'grp.supply-chain.suppliers.index',

                            ]
                        ],

                    ]
                ]
            ];
        }


        if ($user->hasPermissionTo('organisations.view')) {
            $groupNavigation['organisations'] = [
                'label'   => __('Organisations'),
                'icon'    => ['fal', 'fa-building'],
                'root'    => 'grp.organisations.',
                'route'   => [
                    'name' => 'grp.organisations.index'
                ],
                'topMenu' => []
            ];
        }

        if ($user->hasPermissionTo('group-overview')) {
            $groupNavigation['overview'] = [
                'label'   => __('Overview'),
                'icon'    => ['fal', 'fa-mountains'],
                'root'    => 'grp.overview.',
                'route'   => [
                    'name' => 'grp.overview.hub'
                ],
                'topMenu' => []
            ];
        }

        if ($user->hasPermissionTo('sysadmin.view')) {
            $groupNavigation['sysadmin'] = [
                'label'   => __('sysadmin'),
                'icon'    => ['fal', 'fa-users-cog'],
                'root'    => 'grp.sysadmin.',
                'route'   => [
                    'name' => 'grp.sysadmin.dashboard'
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('users'),
                            'icon'  => ['fal', 'fa-terminal'],
                            'root'  => 'grp.sysadmin.users.',
                            'route' => [
                                'name' => 'grp.sysadmin.users.index',

                            ]
                        ],
                        [
                            'label' => __('guests'),
                            'icon'  => ['fal', 'fa-user-alien'],
                            'root'  => 'grp.sysadmin.guests.',
                            'route' => [
                                'name' => 'grp.sysadmin.guests.index',

                            ]
                        ],
                        [
                            'label' => __('system settings'),
                            'icon'  => ['fal', 'fa-cog'],
                            'root'  => 'grp.sysadmin.settings.',
                            'route' => [
                                'name' => 'grp.sysadmin.settings.edit',

                            ]
                        ],
                    ]
                ]
            ];
        }


        return $groupNavigation;
    }
}
