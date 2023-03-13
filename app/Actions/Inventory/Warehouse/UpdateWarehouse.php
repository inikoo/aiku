<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 11:48:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Inventory\Warehouse;

class UpdateWarehouse
{
    use WithActionUpdate;

    public function handle(Warehouse $warehouse, array $modelData): Warehouse
    {
        $warehouse = $this->update($warehouse, $modelData, ['data','settings']);
        WarehouseHydrateUniversalSearch::dispatch($warehouse);
        return $warehouse;
    }
}
