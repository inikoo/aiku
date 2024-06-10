<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Jun 2024 14:05:15 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue;

use App\Models\Catalogue\ProductCategory;

trait WithDepartmentSubNavigation
{
    protected function getDepartmentSubNavigation(ProductCategory $department): array
    {
        return [
            [
                'label'    => $department->name,
                'href'     => [
                    'name'       => 'grp.org.shops.show.catalogue.departments.show',
                    'parameters' => [$this->organisation->slug, $department->shop->slug, $department->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-folder-tree'],
                    'tooltip' => __('Department')
                ]
            ],
            [
                'label'    => __('Families'),
                'number'   => $department->stats->number_current_families,
                'href'     => [
                    'name'       => 'grp.org.shops.show.catalogue.departments.show.families.index',
                    'parameters' => [$this->organisation->slug, $department->shop->slug, $department->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-folder'],
                    'tooltip' => __('families')
                ]
            ],
            [
                'label'    => __('Products'),
                'number'   => $department->stats->number_current_products,
                'href'     => [
                    'name'       => 'grp.org.shops.show.catalogue.departments.show.products.index',
                    'parameters' => [$this->organisation->slug, $department->shop->slug, $department->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-folder'],
                    'tooltip' => __('products')
                ]
            ],
            [
                'label'    => __('Sub departments'),
                'number'   => $department->stats->number_sub_departments,
                'href'     => [
                    'name'       => 'grp.org.shops.show.catalogue.departments.show.sub-departments.index',
                    'parameters' => [$this->organisation->slug, $department->shop->slug, $department->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-folder'],
                    'tooltip' => __('products')
                ]
            ],
        ];
    }

}
