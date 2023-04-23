<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\UI\SysAdmin\SysAdminDashboard;
use App\Models\Auth\User;

trait HasUIUser
{
    public function getBreadcrumbs(User $user): array
    {
        return array_merge(
            (new SysAdminDashboard())->getBreadcrumbs(),
            [
                'sysadmin.users.show' => [
                    'route'           => 'sysadmin.users.show',
                    'routeParameters' => $user->id,
                    'index'           => [
                        'route'   => 'sysadmin.users.index',
                        'overlay' => __("users' list")
                    ],
                    'modelLabel'      => [
                        'label' => __('user')
                    ],
                    'name'            => '@'.$user->username,

                ],
            ]
        );
    }
}
