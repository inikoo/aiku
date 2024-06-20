<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Apr 2023 21:32:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Goods\Stock\Hydrators\StockInitialiseImageID;
use App\Models\SupplyChain\Stock;
use Lorisleiva\Actions\Concerns\AsAction;
use stdClass;

class SyncStockTradeUnitImages
{
    use AsAction;

    public function handle(Stock $stock): Stock
    {
        $images = [];


        foreach ($stock->tradeUnits as $tradeUnit) {
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

        $stock->images()->syncWithoutDetaching($images);

        StockInitialiseImageID::run($stock);


        return $stock;
    }
}
