<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:57:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\Inventory\Location;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class LocationHydrateStockValue implements ShouldBeUnique
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(Location $location): void
    {
        $stockValue=0;
        foreach($location->stocks as $stock) {
            $stockValue+=$stock->pivot->quantity*$stock->unit_value;
        }


        $location->update([
            'stock_value' => $stockValue
        ]);

    }

    public function getJobUniqueId(Location $location): int
    {
        return $location->id;
    }
}
