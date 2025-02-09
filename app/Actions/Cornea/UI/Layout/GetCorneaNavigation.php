<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Feb 2025 19:51:49 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Cornea\UI\Layout;

use App\Models\SupplyChain\SupplierUser;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCorneaNavigation
{
    use AsAction;

    public function handle(SupplierUser $SupplierUser): array
    {
        $corneaNavigation = [];

        $corneaNavigation['dashboard'] = [
            'label' => __('Dashboard'),
            'icon' => ['fal', 'fa-tachometer-alt'],
            'root' => 'cornea.dashboard.',
            'route' => [
                'name' => 'cornea.dashboard.show'
            ],
            'topMenu' => [

            ]
        ];

        return $corneaNavigation;
    }
}
