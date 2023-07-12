<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:54:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Inventory\Location;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class LocationHydrateStocks implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Location $location): void
    {
        $numberStockSlots = $location->stocks()->count();
        $stats            = [
            'number_stock_slots' => $numberStockSlots,
        ];

        $location->stats->update($stats);

    }

    public function getJobUniqueId(Location $location): int
    {
        return $location->id;
    }
}
