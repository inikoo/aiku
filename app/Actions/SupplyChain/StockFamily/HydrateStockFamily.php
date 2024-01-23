<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily;

use App\Actions\HydrateModel;
use App\Models\SupplyChain\StockFamily;
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


    protected function getModel(string $slug): StockFamily
    {
        return StockFamily::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return StockFamily::withTrashed()->get();
    }
}
