<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 21:40:23 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Actions\HydrateModel;
use App\Models\Inventory\StockFamily;

use Illuminate\Support\Collection;

class HydrateStockFamily extends HydrateModel
{
    public string $commandSignature = 'hydrate:stock-family {organisations?*} {--i|id=} ';

    public function handle(StockFamily $stockFamily): void
    {
        $this->stocksStats($stockFamily);
    }

    public function stocksStats(StockFamily $stockFamily)
    {
    }


    protected function getModel(int $id): StockFamily
    {
        return StockFamily::find($id);
    }

    protected function getAllModels(): Collection
    {
        return StockFamily::withTrashed()->get();
    }
}
