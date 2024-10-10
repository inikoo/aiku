<?php
/*
 * author Arya Permana - Kirin
 * created on 09-10-2024-15h-50m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Web\Webpage;

use App\Models\Catalogue\Shop;

trait WithWebpageSubnavigation
{
    protected function getWebpageNavigation(Shop $shop): array
    {
        return [         
            [
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.tree',
                    'parameters' => [$shop->organisation->slug, $shop->slug, $shop->website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-code-branch'],
                    'tooltip' => __('Webpage Tree')
                ]
            ],
            [
                'isAnchor'   => true,
                'label'    => __('All'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index',
                    'parameters' => [$shop->organisation->slug, $shop->slug, $shop->website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('All Webpages')
                ]
            ],
            [
                'label'    => __('Shop'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index.type.shop',
                    'parameters' => [$shop->organisation->slug, $shop->slug, $shop->website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Shop Webpages')
                ]
            ],
            [
                'label'    => __('Content'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index.type.content',
                    'parameters' => [$shop->organisation->slug, $shop->slug, $shop->website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Content Webpages')
                ]
            ],
            [
                'label'    => __('Small Print'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index.type.small-print',
                    'parameters' => [$shop->organisation->slug, $shop->slug, $shop->website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Small Print Webpages')
                ]
            ],
            [
                'label'    => __('Checkout'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index.type.checkout',
                    'parameters' => [$shop->organisation->slug, $shop->slug, $shop->website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Checkout Webpages')
                ]
            ],
        ];
    }

}
