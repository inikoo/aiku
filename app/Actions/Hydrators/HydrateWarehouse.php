<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 23:02:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Hydrators;

use App\Models\Inventory\Warehouse;
use Illuminate\Support\Collection;


class HydrateWarehouse extends HydrateModel
{

    public string $commandSignature = 'hydrate:warehouse {organisation_code?} {id?}';


    public function handle(Warehouse $warehouse): void
    {
        $this->warehouseAreas($warehouse);
        $this->locations($warehouse);
    }

    public function warehouseAreas(Warehouse $warehouse): void
    {
        $warehouse->stats->update(
            [
                'number_warehouse_areas'=>$warehouse->warehouseAreas()->count()

            ]
        );
    }

    public function locations(Warehouse $warehouse): void
    {

        $numberLocations=$warehouse->locations->count();
        $numberOperationalLocations=$warehouse->locations->where('state','operational')->count();
        $warehouse->stats->update(
            [
                'number_locations'=>$numberLocations,
                'number_locations_state_operational'=>$numberOperationalLocations,
                'number_locations_state_broken'=>$numberLocations-$numberOperationalLocations

            ]
        );
    }



    protected function getModel(int $id): Warehouse
    {
        return Warehouse::where('organisation_id',$this->organisation->id)->find($id);
    }

    protected function getAllModels(): Collection
    {
        return Warehouse::where('organisation_id',$this->organisation->id)->get();
    }

}


