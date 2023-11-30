<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:49:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea\Hydrators;

use App\Models\Inventory\WarehouseArea;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseAreaHydrateStocks implements ShouldBeUnique
{
    use AsAction;


    public function handle(WarehouseArea $warehouseArea): void
    {
        $stockValue = 0;
        foreach ($warehouseArea->locations as $location) {
            $stockValue = +$location->stocks()->sum('value');
        }

        $warehouseArea->stats->update(
            [
                'stock_value' => $stockValue
            ]
        );
    }

    public function getJobUniqueId(WarehouseArea $warehouseArea): int
    {
        return $warehouseArea->id;
    }
}
