<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Jun 2024 14:05:15 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue;

use App\Models\Catalogue\ProductCategory;

trait WithFamilySubNavigation
{
    protected function getFamilySubNavigation(ProductCategory $family): array
    {
        return [
            [
                'label'    => $family->code,
                'href'     => [
                    'name'       => 'grp.org.shops.show.catalogue.families.show',
                    'parameters' => [$this->organisation->slug, $family->shop->slug, $family->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-folder'],
                    'tooltip' => __('family')
                ]
            ],

            [
                'label'    => __('Products'),
                'number'   => $family->stats->number_products,
                'href'     => [
                    'name'       => 'grp.org.shops.show.catalogue.families.show.products.index',
                    'parameters' => [$this->organisation->slug, $family->shop->slug, $family->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-cube'],
                    'tooltip' => __('products')
                ]
            ],
        ];
    }

}
