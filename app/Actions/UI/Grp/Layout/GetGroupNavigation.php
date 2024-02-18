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


        if ($user->hasPermissionTo('supply-chain.view')) {
            $groupNavigation['supply-chain'] = [
                'label'   => __('Supply Chain'),
                'icon'    => ['fal', 'fa-box-usd'],
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

        if ($user->hasPermissionTo('supply-chain.view')) {
            $groupNavigation['organisations'] = [
                'label'   => __('Organisations'),
                'icon'    => ['fal', 'fa-building'],
                'route'   => [
                    'name' => 'grp.orgs.index'
                ],
                'topMenu' => []
            ];
        }

        if ($user->hasPermissionTo('sysadmin.view')) {
            $groupNavigation['sysadmin'] = [
                'label'   => __('sysadmin'),
                'icon'    => ['fal', 'fa-users-cog'],
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
