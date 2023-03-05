<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 21:01:11 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Models\Inventory\StockFamily;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreStockFamily
{
    use AsAction;

    public function handle($modelData): StockFamily
    {
        /** @var StockFamily $stockFamily */
        $stockFamily = StockFamily::create($modelData);
        $stockFamily->stats()->create();

        return $stockFamily;
    }
}
