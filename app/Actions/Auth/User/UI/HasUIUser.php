<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

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
