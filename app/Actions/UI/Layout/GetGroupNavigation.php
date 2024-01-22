<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 08 Dec 2023 22:08:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

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
                            'route' => [
                                'name' => 'grp.supply-chain.agents.index',

                            ]
                        ],
                        [
                            'label' => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
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
                'icon'    => ['fal', 'fa-user'],
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
                            'route' => [
                                'name' => 'grp.sysadmin.users.index',

                            ]
                        ],
                        [
                            'label' => __('guests'),
                            'icon'  => ['fal', 'fa-user-alien'],
                            'route' => [
                                'name' => 'grp.sysadmin.guests.index',

                            ]
                        ],
                        [
                            'label' => __('system settings'),
                            'icon'  => ['fal', 'fa-cog'],
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
