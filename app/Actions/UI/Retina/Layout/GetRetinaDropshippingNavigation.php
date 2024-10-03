<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Feb 2024 22:34:23 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Layout;

use App\Models\CRM\WebUser;
use Lorisleiva\Actions\Concerns\AsAction;

class GetRetinaDropshippingNavigation
{
    use AsAction;

    public function handle(WebUser $webUser): array
    {
        $groupNavigation = [];

        $groupNavigation['dashboard'] = [
            'label' => __('Dashboard'),
            'icon' => ['fal', 'fa-tachometer-alt'],
            'root' => 'retina.dashboard.',
            'route' => [
                'name' => 'retina.dashboard.show'
            ],
            'topMenu' => [

            ]
        ];

        $groupNavigation['platform'] = [
            'label' => __('Platform'),
            'icon' => ['fal', 'fa-parachute-box'],
            'root' => 'retina.dropshipping.platform.',
            'route' => [
                'name' => 'retina.dropshipping.platform.dashboard'
            ]
        ];

        $groupNavigation['portfolios'] = [
            'label' => __('Portfolios'),
            'icon' => ['fal', 'fa-cube'],
            'root' => 'retina.dropshipping.portfolios.',
            'route' => [
                'name' => 'retina.dropshipping.portfolios.index'
            ]
        ];

        $groupNavigation['client'] = [
            'label' => __('Client'),
            'icon' => ['fal', 'fa-user-friends'],
            'root' => 'retina.dropshipping.client.',
            'route' => [
                'name' => 'retina.dropshipping.client.index'
            ],
        ];

        return $groupNavigation;
    }
}
