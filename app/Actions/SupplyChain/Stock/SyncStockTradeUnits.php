<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 18:48:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Stock;

use App\Models\SupplyChain\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncStockTradeUnits
{
    use AsAction;

    public function handle(Stock $stock, array $tradeUnitsData): Stock
    {
        $stock->tradeUnits()->sync($tradeUnitsData);
        // todo run over all OrfStock and hydrate that stuff

        return $stock;
    }
}
