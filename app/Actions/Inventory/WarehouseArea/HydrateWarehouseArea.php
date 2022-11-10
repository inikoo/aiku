<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:37:19 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\HydrateModel;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Support\Collection;


class HydrateWarehouseArea extends HydrateModel
{

    public string $commandSignature = 'hydrate:warehouse-area {tenants?*} {--s|source_id=}';

    public function handle(WarehouseArea $warehouseArea): void
    {

        $this->locations($warehouseArea);
    }

    public function locations(WarehouseArea $warehouseArea): void
    {
        if(!$warehouseArea->id){
            return;
        }
        $warehouseArea->stats->update(
            [
                'number_locations'=>$warehouseArea->locations->count(),

            ]
        );
    }

    protected function getModel(int $id): WarehouseArea
    {
        return WarehouseArea::find($id);
    }

    protected function getAllModels(): Collection
    {
        return WarehouseArea::all();
    }

}


