<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Product\Hydrators;

use App\Models\Market\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateUniversalSearch
{
    use AsAction;


    public function handle(Product $product): void
    {
        $product->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $product->group_id,
                'organisation_id'   => $product->organisation_id,
                'organisation_slug' => $product->organisation->slug,
                'shop_id'           => $product->shop_id,
                'shop_slug'         => $product->shop->slug,
                'section'           => 'shops',
                'title'             => $product->name,
                'description'       => $product->code
            ]
        );
    }

}
