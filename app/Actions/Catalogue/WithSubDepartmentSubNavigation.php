<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Jun 2024 14:05:15 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue;

use App\Models\Catalogue\ProductCategory;

trait WithSubDepartmentSubNavigation
{
    protected function getSubDepartmentSubNavigation(ProductCategory $subDepartment): array
    {
        return [
            [
                'label'    => $subDepartment->code,
                'href'     => [
                    'name'       => 'grp.org.shops.show.catalogue.departments.show.sub-departments.show',
                    'parameters' => [$this->organisation->slug, $subDepartment->shop->slug, $subDepartment->parent->slug,  $subDepartment->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-folder'],
                    'tooltip' => __('sub department')
                ]
            ],

            // [
            //     'label'    => __('Products'),
            //     'number'   => $subDepartment->stats->number_products,
            //     'href'     => [
            //         'name'       => 'grp.org.shops.show.catalogue.families.show.products.index',
            //         'parameters' => [$this->organisation->slug, $subDepartment->shop->slug, $subDepartment->slug]
            //     ],
            //     'leftIcon' => [
            //         'icon'    => ['fal', 'fa-cube'],
            //         'tooltip' => __('products')
            //     ]
            // ],
        ];
    }

}
