<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 21:01:30 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Actions\WithActionUpdate;
use App\Models\Inventory\StockFamily;

class UpdateStockFamily
{
    use WithActionUpdate;

    public function handle(StockFamily $stockFamily, array $modelData): StockFamily
    {
        return $this->update($stockFamily, $modelData, ['data']);
    }
}
