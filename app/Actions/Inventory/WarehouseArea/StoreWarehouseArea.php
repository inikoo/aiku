<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 12:34:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\StoreModelAction;
use App\Models\Inventory\WarehouseArea;
use App\Models\Utils\ActionResult;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWarehouseArea extends StoreModelAction
{
    use AsAction;

    public function handle(Warehouse $warehouse, array $modelData): ActionResult
    {
        $modelData['organisation_id']=$warehouse->organisation_id;

        /** @var WarehouseArea $warehouseArea */
        $warehouseArea= $warehouse->warehouseAreas()->create($modelData);
        $warehouseArea->stats()->create();
        return $this->finalise($warehouseArea);
    }
}
