<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Product\UI;

use App\Actions\Marketing\Shop\ShowShop;
use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Shop;

trait HasUIProducts
{
    public function getBreadcrumbs(string $routeName, Shop|Tenant|Department $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('products')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'products.index'            => $headCrumb(),
            'shops.show.products.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }
}
