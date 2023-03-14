<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Department\UI;

use App\Actions\Marketing\Shop\IndexShops;
use App\Models\Marketing\Department;

trait HasUIDepartment
{
    public function getBreadcrumbs(Department $department): array
    {
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $department->id,
                    'name'            => $department->code,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Departments list')
                    ],
                    'modelLabel' => [
                        'label' => __('department')
                    ],
                ],
            ]
        );
    }
}
