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

    public function handle(WebUser $webUser, $request): array
    {
        $customer = $webUser->customer;
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


        $platforms_navigation = [];
        foreach (
            $customer->platforms()->get() as $platform
        ) {
            $platforms_navigation[$platform->slug] = [
                'type'          => $platform->type,
                'subNavigation' => GetRetinaDropshippingPlatformNavigation::run($webUser, $request)
            ];
        }

        $groupNavigation['platforms_navigation'] = [
            'platforms_navigation'       => [
                'label'      => __('platforms'),
                'icon'       => "fal fa-store-alt",
                'navigation' => $platforms_navigation
            ],
        ];

        return $groupNavigation;
    }
}
