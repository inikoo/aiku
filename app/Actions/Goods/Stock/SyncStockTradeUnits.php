<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock;

use App\Actions\Goods\Stock\Hydrators\StockHydrateWeightFromTradeUnits;
use App\Models\SupplyChain\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncStockTradeUnits
{
    use AsAction;

    public function handle(Stock $stock, array $tradeUnitsData): Stock
    {
        $stock->tradeUnits()->sync($tradeUnitsData);

        StockHydrateWeightFromTradeUnits::dispatch($stock);


        return $stock;
    }
}
