<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:33:19 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\Search;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Product $product): void
    {
        if ($product->trashed()) {
            $product->universalSearch()->delete();
            return;
        }

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
