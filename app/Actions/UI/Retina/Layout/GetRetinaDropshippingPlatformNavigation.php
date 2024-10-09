<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Feb 2024 22:34:23 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Layout;

use App\Models\CRM\WebUser;
use Lorisleiva\Actions\Concerns\AsAction;

class GetRetinaDropshippingPlatformNavigation
{
    use AsAction;

    public function handle(WebUser $webUser, $request): array
    {
        $platformNavigation = [];

        if ($webUser->customer->shopifyUser) {
            $platformNavigation['portfolios'] = [
                'label' => __('Portfolios'),
                'icon' => ['fal', 'fa-cube'],
                'root' => 'retina.dropshipping.portfolios.',
                'route' => [
                    'name' => 'retina.dropshipping.portfolios.index'
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'label' => __('My Portfolio'),
                            'icon' => ['fal', 'fa-cube'],
                            'root' => 'retina.dropshipping.portfolios.index',
                            'route' => [
                                'name' => 'retina.dropshipping.portfolios.index'
                            ],
                        ],
                        [
                            'label' => __('All Products'),
                            'icon' => ['fal', 'fa-cube'],
                            'root' => 'retina.dropshipping.portfolios.products.index',
                            'route' => [
                                'name' => 'retina.dropshipping.portfolios.products.index'
                            ],
                        ]
                    ]
                ]
            ];
        }

        $platformNavigation['client'] = [
            'label' => __('Client'),
            'icon' => ['fal', 'fa-user-friends'],
            'root' => 'retina.dropshipping.client.',
            'route' => [
                'name' => 'retina.dropshipping.client.index'
            ],
        ];

        return $platformNavigation;
    }
}
