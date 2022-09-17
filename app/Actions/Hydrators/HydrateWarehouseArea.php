<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 23:21:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Hydrators;

use App\Models\Inventory\WarehouseArea;
use Illuminate\Support\Collection;


class HydrateWarehouseArea extends HydrateModel
{

    public string $commandSignature = 'hydrate:warehouse-area {organisation_code} {id?}';

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
        return WarehouseArea::get();
    }

}


