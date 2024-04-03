<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:37:32 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\HydrateModel;
use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateLocations;
use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateQuantityInLocations;
use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateValueInLocations;
use App\Models\SupplyChain\Stock;
use Illuminate\Support\Collection;

class HydrateOrgStock extends HydrateModel
{
    public string $commandSignature = 'org-stocks:hydrate {organisations?*} {--s|slugs=} ';


    public function handle(Stock $stock): void
    {
        OrgStockHydrateLocations::run($stock);
        OrgStockHydrateQuantityInLocations::run($stock);
        OrgStockHydrateValueInLocations::run($stock);
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
