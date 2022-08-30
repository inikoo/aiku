<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 12:35:41 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\UpdateModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\Concerns\AsAction;


class UpdateWarehouseArea extends UpdateModelAction
{
    use AsAction;

    public function handle(WarehouseArea $warehouseArea, array $modelData): ActionResult
    {
        $this->model=$warehouseArea;
        $this->modelData=$modelData;
        return $this->updateAndFinalise(jsonFields:['data']);
    }

}

