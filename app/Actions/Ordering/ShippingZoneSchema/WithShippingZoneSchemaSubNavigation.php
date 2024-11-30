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
        $navigation = [
            [
                'isAnchor' => true,
                'label'    => __('Schemas'),
                'route'     => [
                        'name'       => 'grp.org.shops.show.billables.shipping.index',
                        'parameters' => [$this->organisation->slug, $shop->slug]
                    ],
                    'leftIcon' => [
                        'icon'    => ['fal', 'fa-shipping-fast'],
                        'tooltip' => __('Schemas')
                        ]
            ],
        ];
        if ($shop->currentShippingZoneSchema) {
            $current = [
                'label'    => __('Current'),
                'route'     => [
                    'name'       => 'grp.org.shops.show.billables.shipping.show',
                    'parameters' => [$this->organisation->slug, $shop->slug, $shop->currentShippingZoneSchema->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-cube'],
                    'tooltip' => __('Current Schema')
                ]
                ];
            array_push($navigation, $current);
        }

        if ($shop->discountShippingZoneSchema) {
            $discount = [
                'label'    => __('Discount'),
                'route'     => [
                    'name'       => 'grp.org.shops.show.billables.shipping.show',
                    'parameters' => [$this->organisation->slug, $shop->slug, $shop->discountShippingZoneSchema->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-cube'],
                    'tooltip' => __('Discount Schema')
                ]
                ];
            array_push($navigation, $discount);
        }

        return $navigation;
    }

}
