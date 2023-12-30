<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:37:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\HydrateModel;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateLocations;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateStocks;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateWarehouseAreas;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Collection;

class HydrateWarehouse extends HydrateModel
{
    public string $commandSignature = 'hydrate:warehouses {organisations?*} {--i|id=}';

    public function handle(Warehouse $warehouse): void
    {
        WarehouseHydrateLocations::run($warehouse);
        WarehouseHydrateStocks::run($warehouse);
        WarehouseHydrateWarehouseAreas::run($warehouse);
    }

    protected function getModel(int $id): Warehouse
    {
        return Warehouse::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Warehouse::all();
    }
}
