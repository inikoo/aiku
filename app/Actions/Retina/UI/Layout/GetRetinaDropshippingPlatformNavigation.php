<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Feb 2024 22:34:23 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\UI\Layout;

use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\CRM\WebUser;
use App\Models\Dropshipping\Platform;
use Lorisleiva\Actions\Concerns\AsAction;

class GetRetinaDropshippingPlatformNavigation
{
    use AsAction;

    public function handle(WebUser $webUser, Platform $platform): array
    {
        $platformNavigation = [];

        if ($webUser->customer->shopifyUser) {
            $tabs = [];

            if (!$webUser->customer->fulfilmentCustomer or $platform->type !== PlatformTypeEnum::SHOPIFY) {
                $tabs = [
                    [
                        'label' => __('All Products'),
                        'icon' => ['fal', 'fa-cube'],
                        'root' => 'retina.dropshipping.platforms.portfolios.products.index',
                        'route' => [
                            'name' => 'retina.dropshipping.platforms.portfolios.products.index',
                            'parameters' => [$platform->slug]
                        ],
                    ]
                ];
            }

            $platformNavigation['portfolios'] = [
                'label' => __('Portfolios'),
                'icon' => ['fal', 'fa-cube'],
                'root' => 'retina.dropshipping.platforms.portfolios.',
                'route' => [
                    'name' => 'retina.dropshipping.platforms.portfolios.index',
                    'parameters' => [$platform->slug]
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('My Portfolio'),
                            'icon' => ['fal', 'fa-cube'],
                            'root' => 'retina.dropshipping.platforms.portfolios.index',
                            'route' => [
                                'name' => 'retina.dropshipping.platforms.portfolios.index',
                                'parameters' => [$platform->slug]
                            ],
                        ],
                        ...$tabs
                    ]
                ]
            ];
        }

        $platformNavigation['client'] = [
            'label' => __('Client'),
            'icon' => ['fal', 'fa-user-friends'],
            'root' => 'retina.dropshipping.platforms.client.',
            'route' => [
                'name' => 'retina.dropshipping.platforms.client.index',
                'parameters' => [$platform->slug]
            ],
        ];

        $platformNavigation['orders'] = [
            'label' => __('Orders'),
            'icon' => ['fal', 'fa-money-bill-wave'],
            'root' => 'retina.dropshipping.platforms.orders.',
            'route' => [
                'name' => 'retina.dropshipping.platforms.orders.index',
                'parameters' => [$platform->slug]
            ],
        ];

        return $platformNavigation;
    }
}
