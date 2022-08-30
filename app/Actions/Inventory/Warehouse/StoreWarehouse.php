<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:25:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\StoreModelAction;
use App\Models\Inventory\Warehouse;
use App\Models\Organisations\Organisation;
use App\Models\Utils\ActionResult;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWarehouse extends StoreModelAction
{
    use AsAction;

    public function handle(Organisation $organisation,$modelData): ActionResult
    {
        /** @var Warehouse $warehouse */
        $warehouse = $organisation->warehouses()->create($modelData);
        $warehouse->stats()->create();

        return $this->finalise($warehouse);
    }
}
