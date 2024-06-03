<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 May 2024 00:04:36 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */
namespace App\Actions\Catalogue\ProductCategory;

use App\Models\Catalogue\ProductCategory;
use App\Models\HumanResources\Workplace;

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
                'number'   => $department->stats->number_families,
                'href'     => [
                    'name'       => 'grp.org.shops.show.catalogue.departments.families.index',
                    'parameters' => [$this->organisation->slug, $department->shop->slug, $department->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-folder'],
                    'tooltip' => __('families')
                ]
            ],
            // [
            //     'label'    => __('Clockings'),
            //     'number'   => $workplace->stats->number_clockings,
            //     'href'     => [
            //         'name'       => 'grp.org.hr.workplaces.show.clockings.index',
            //         'parameters' => [$this->organisation->slug, $workplace->slug]
            //     ],
            //     'leftIcon' => [
            //         'icon'    => ['fal', 'clock'],
            //         'tooltip' => __('clockings')
            //     ]
            // ]
        ];
    }

}
