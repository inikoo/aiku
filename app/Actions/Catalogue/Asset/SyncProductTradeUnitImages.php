<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Apr 2023 21:32:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Asset;

use App\Actions\Catalogue\Asset\Hydrators\ProductInitialiseImageID;
use App\Models\Catalogue\Asset;
use Lorisleiva\Actions\Concerns\AsAction;
use stdClass;

class SyncProductTradeUnitImages
{
    use AsAction;

    public function handle(Asset $product): Asset
    {
        $images = [];


        foreach ($product->tradeUnits as $tradeUnit) {
            foreach ($tradeUnit->media as $media) {
                $images[$media->id] = [
                    'owner_type'      => 'TradeUnit',
                    'owner_id'        => $tradeUnit->id,
                    'type'            => 'image',
                    'data'            => json_encode(new stdClass())
                ];
            }
        }
        /*
         * To this to work delete TradeUnit images should delete this record as well
         */

        $product->images()->syncWithoutDetaching($images);

        ProductInitialiseImageID::run($product);


        return $product;
    }
}
