<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:37:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\HydrateModel;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Collection;

class HydrateWarehouse extends HydrateModel
{
    public string $commandSignature = 'hydrate:warehouse {tenants?*} {--i|id=}';


    public function handle(Warehouse $warehouse): void
    {
        $this->warehouseAreas($warehouse);
        $this->locations($warehouse);
        $this->stock($warehouse);
    }

    public function stock(Warehouse $warehouse): void
    {
        $stockValue = 0;
        foreach ($warehouse->locations as $location) {
            $stockValue =+ $location->stocks()->sum('value');
        }

        $warehouse->stats->update(
            [
                'stock_value' => $stockValue
            ]
        );
    }

    public function warehouseAreas(Warehouse $warehouse): void
    {
        $warehouse->stats->update(
            [
                'number_warehouse_areas'=> $warehouse->warehouseAreas()->count()

            ]
        );
    }

    public function locations(Warehouse $warehouse): void
    {
        $numberLocations           =$warehouse->locations->count();
        $numberOperationalLocations=$warehouse->locations->where('state', 'operational')->count();
        $warehouse->stats->update(
            [
                'number_locations'                  => $numberLocations,
                'number_locations_state_operational'=> $numberOperationalLocations,
                'number_locations_state_broken'     => $numberLocations-$numberOperationalLocations

            ]
        );
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
