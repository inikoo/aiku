<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:36:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Actions\HydrateModel;
use App\Models\Inventory\Location;
use Illuminate\Support\Collection;


class HydrateLocation extends HydrateModel
{

    public string $commandSignature = 'hydrate:location {tenants?*} {--i|id=}';


    public function handle(Location $location): void
    {
        $this->stocks($location);
    }

    public function stocks(Location $location): void
    {
        $numberStockSlots = $location->stocks->count();
        $stats = [
            'number_stock_slots' => $numberStockSlots,
        ];

        $location->stats->update($stats);
    }



    protected function getModel(int $id): Location
    {
        return Location::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Location::withTrashed()->all();
    }

}


