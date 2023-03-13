<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Product\UI;

use App\Actions\Marketing\Shop\IndexShops;
use App\Models\Marketing\Product;

trait HasUIProduct
{
    public function getBreadcrumbs(Product $product): array
    {
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $product->id,
                    'name'            => $product->code,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Products list')
                    ],
                    'modelLabel' => [
                        'label' => __('product')
                    ],
                ],
            ]
        );
    }
}
