<?php
/*
 * author Arya Permana - Kirin
 * created on 18-10-2024-13h-39m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\ShippingZoneSchema;

use App\Models\Catalogue\Shop;

trait WithShippingZoneSchemaSubNavigation
{
    protected function getShippingZoneSchemaSubNavigation(Shop $shop): array
    {
        return [
            [
                'isAnchor' => true,
                'label'    => __('Schemas'),
                'href'     => [
                        'name'       => 'grp.org.shops.show.assets.shipping.index',
                        'parameters' => [$this->organisation->slug, $shop->slug]
                    ],
                    'leftIcon' => [
                        'icon'    => ['fal', 'fa-shipping-fast'],
                        'tooltip' => __('Schemas')
                        ]
            ],
            [
                'label'    => __('Current'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.assets.shipping.show',
                    'parameters' => [$this->organisation->slug, $shop->slug, $shop->currentShippingZoneSchema->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-cube'],
                    'tooltip' => __('Current Schema')
                ]
            ],
            [
                'label'    => __('Discount'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.assets.shipping.show',
                    'parameters' => [$this->organisation->slug, $shop->slug, $shop->discountShippingZoneSchema->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-cube'],
                    'tooltip' => __('Discount Schema')
                ]
            ],
        ];
    }

}
