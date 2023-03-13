<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:05:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\Inventory\Stock\Hydrators\StockHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Inventory\Stock;

class UpdateStock
{
    use WithActionUpdate;

    public function handle(Stock $stock, array $modelData): Stock
    {
        $stock = $this->update($stock, $modelData, ['data', 'settings']);
        StockHydrateUniversalSearch::dispatch($stock);

        return $stock;
    }
}
