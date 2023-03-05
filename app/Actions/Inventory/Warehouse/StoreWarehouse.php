<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:25:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Warehouse;

use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWarehouse
{
    use AsAction;

    public function handle($modelData): Warehouse
    {
        $warehouse = Warehouse::create($modelData);
        $warehouse->stats()->create();

        return $warehouse;
    }
}
