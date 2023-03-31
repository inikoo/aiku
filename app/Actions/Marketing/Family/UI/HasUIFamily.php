<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Family\UI;

use App\Actions\Marketing\Shop\IndexShops;
use App\Models\Marketing\Family;

trait HasUIFamily
{
    public function getBreadcrumbs(Family $family): array
    {
        return [];
        /*
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $family->id,
                    'name'            => $family->code,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Families list')
                    ],
                    'modelLabel' => [
                        'label' => __('family')
                    ],
                ],
            ]
        );
        */
    }
}
