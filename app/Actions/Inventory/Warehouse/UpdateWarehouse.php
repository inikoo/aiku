<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 11:48:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\UpdateModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsAction;


class UpdateWarehouse extends UpdateModelAction
{
    use AsAction;

    public function handle(Warehouse $warehouse, array $modelData): ActionResult
    {
        $this->model=$warehouse;
        $this->modelData=$modelData;
        return $this->updateAndFinalise(jsonFields:['data', 'settings']);
    }
}
