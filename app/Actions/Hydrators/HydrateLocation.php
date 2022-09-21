<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 17 Mar 2022 01:19:44 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Hydrators;

use App\Models\Inventory\Location;
use Illuminate\Support\Collection;


class HydrateLocation extends HydrateModel
{

    public string $commandSignature = 'hydrate:location {tenant_code?} {id?}';


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


