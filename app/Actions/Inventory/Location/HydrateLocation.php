<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:36:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Actions\HydrateModel;
use App\Actions\Inventory\Location\Hydrators\LocationHydratePallets;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateStocks;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateStockValue;
use App\Models\Inventory\Location;
use Illuminate\Support\Collection;

class HydrateLocation extends HydrateModel
{
    public string $commandSignature = 'hydrate:locations {organisations?*} {--s|slugs=}';


    public function handle(Location $location): void
    {
        LocationHydrateStocks::run($location);
        LocationHydrateStockValue::run($location);
        LocationHydratePallets::run($location);
    }

    protected function getModel(string $slug): Location
    {
        return Location::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Location::withTrashed()->get();
    }
}
