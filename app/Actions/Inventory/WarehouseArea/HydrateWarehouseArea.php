<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:37:19 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\HydrateModel;
use App\Actions\Inventory\WarehouseArea\Hydrators\WarehouseAreaHydrateLocations;
use App\Actions\Inventory\WarehouseArea\Hydrators\WarehouseAreaHydrateStocks;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Support\Collection;

class HydrateWarehouseArea extends HydrateModel
{
    public string $commandSignature = 'hydrate:warehouse-areas {organisations?*} {--i|slugs=}';

    public function handle(WarehouseArea $warehouseArea): void
    {
        WarehouseAreaHydrateLocations::run($warehouseArea);
        WarehouseAreaHydrateStocks::run($warehouseArea);
    }


    protected function getModel(string $slug): WarehouseArea
    {
        return WarehouseArea::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return WarehouseArea::all();
    }
}
