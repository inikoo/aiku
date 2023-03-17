<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\UI\SysAdmin\SysAdminDashboard;

trait HasUIUsers
{
    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new SysAdminDashboard())->getBreadcrumbs(),
            [
                'sysadmin.users.index' => [
                    'route'      => 'sysadmin.users.index',
                    'modelLabel' => [
                        'label' => __('users')
                    ],
                ],
            ]
        );
    }
}
