<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:54:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location\Hydrators;

use App\Models\Inventory\Location;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class LocationHydrateStocks
{
    use AsAction;


    private Location $location;
    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->location->id))->dontRelease()];
    }


    public function handle(Location $location): void
    {
        $numberStockSlots = $location->orgStocks()->count();
        $stats            = [
            'number_stock_slots' => $numberStockSlots,
        ];

        $location->stats()->update($stats);

    }

}
