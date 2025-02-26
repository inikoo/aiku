<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Feb 2024 22:34:23 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\UI\Layout;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\CRM\WebUser;
use Lorisleiva\Actions\Concerns\AsAction;

class GetRetinaDropshippingNavigation
{
    use AsAction;

    public function handle(WebUser $webUser): array
    {
        $customer = $webUser->customer;
        $groupNavigation = [];

        if ($customer?->shop?->type === ShopTypeEnum::DROPSHIPPING) {
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

            $groupNavigation['portfolios'] = [
                'label' => __('Portfolio'),
                'icon' => ['fal', 'fa-pallet'],
                'root' => 'retina.dashboard.',
                'route' => [
                    'name' => 'retina.dashboard.show'
                ],
                'topMenu' => [

                ]
            ];

            $groupNavigation['clients'] = [
                'label' => __('Clients'),
                'icon' => ['fal', 'fa-user'],
                'root' => 'retina.dashboard.',
                'route' => [
                    'name' => 'retina.dashboard.show'
                ],
                'topMenu' => [

                ]
            ];

            $groupNavigation['orders'] = [
                'label' => __('Orders'),
                'icon' => ['fal', 'fa-box'],
                'root' => 'retina.dashboard.',
                'route' => [
                    'name' => 'retina.dashboard.show'
                ],
                'topMenu' => [

                ]
            ];
        }

        $groupNavigation['platform'] = [
            'label' => __('Channels'),
            'icon' => ['fal', 'fa-parachute-box'],
            'root' => 'retina.dropshipping.platform.',
            'route' => [
                'name' => 'retina.dropshipping.platform.dashboard'
            ]
        ];

        $platforms_navigation = [];
        foreach (
            $customer->platforms()->get() as $platform
        ) {
            $platforms_navigation[$platform->slug] = [
                'type'          => $platform->type,
                'subNavigation' => GetRetinaDropshippingPlatformNavigation::run($webUser)
            ];
        }

        if ($webUser->customer->shopifyUser) {
            $groupNavigation['platforms_navigation'] = [
                'platforms_navigation'       => [
                    'label'      => __('platforms'),
                    'icon'       => "fal fa-store-alt",
                    'navigation' => $platforms_navigation
                ],
            ];
        }

        return $groupNavigation;
    }
}
