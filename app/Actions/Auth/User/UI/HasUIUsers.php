<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

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
