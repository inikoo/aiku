<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:37:32 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\HydrateModel;
use App\Actions\Inventory\Stock\Hydrators\StockHydrateLocations;
use App\Actions\Inventory\Stock\Hydrators\StockHydrateQuantityInLocations;
use App\Actions\Inventory\Stock\Hydrators\StockHydrateValueInLocations;
use App\Models\Inventory\Stock;
use Illuminate\Support\Collection;

class HydrateStock extends HydrateModel
{
    public string $commandSignature = 'hydrate:stock {organisations?*} {--i|id=} ';


    public function handle(Stock $stock): void
    {
        StockHydrateLocations::run($stock);
        StockHydrateQuantityInLocations::run($stock);
        StockHydrateValueInLocations::run($stock);
    }



    protected function getModel(string $slug): Stock
    {
        return Stock::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Stock::withTrashed()->get();
    }
}
