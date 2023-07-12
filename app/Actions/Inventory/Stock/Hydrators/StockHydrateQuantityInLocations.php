<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 02 Jun 2023 21:00:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Inventory\Stock;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class StockHydrateQuantityInLocations implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;


    public function handle(Stock $stock): void
    {
        $stock->update([
            'quantity_in_locations' =>
                $stock->locations()->sum('quantity')
        ]);
    }

    public function getJobUniqueId(Stock $stock): int
    {
        return $stock->id;
    }
}
