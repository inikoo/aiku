<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 12:35:41 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\WithActionUpdate;
use App\Models\Inventory\WarehouseArea;

class UpdateWarehouseArea
{
    use WithActionUpdate;

    public function handle(WarehouseArea $warehouseArea, array $modelData): WarehouseArea
    {
        return $this->update($warehouseArea, $modelData, ['data']);
    }
}
