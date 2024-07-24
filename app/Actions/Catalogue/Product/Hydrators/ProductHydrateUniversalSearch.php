<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\Hydrators;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Product $product): void
    {

        $shop=$product->shop;

        $modelData = [
            'group_id'                    => $product->group_id,
            'organisation_id'             => $product->organisation_id,
            'organisation_slug'           => $product->organisation->slug,
            'shop_id'                     => $product->shop_id,
            'shop_slug'                   => $product->shop->slug,
            'sections'                    => ['catalogue'],
            'haystack_tier_1'             => $product->code,
            'haystack_tier_2'             => $product->name.' '.$product->description
        ];

        if($shop->type==ShopTypeEnum::FULFILMENT) {
            $modelData['fulfilment_id']     = $shop->fulfilment->id;
            $modelData['fulfilment_slug']   = $shop->fulfilment->slug;
        }


        $product->universalSearch()->updateOrCreate(
            [],
            $modelData
        );
    }

}
