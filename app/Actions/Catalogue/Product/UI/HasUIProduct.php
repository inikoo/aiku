<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Actions\Catalogue\Shop\UI\IndexShops;
use App\Models\Catalogue\Product;

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
                        'route'   => 'grp.org.shops.index',
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
