<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:05:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\UpdateModelAction;
use App\Models\Utils\ActionResult;
use App\Actions\WithUpdate;
use App\Models\Inventory\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateStock extends UpdateModelAction
{
    use AsAction;
    use WithUpdate;

    public function handle(Stock $stock, array $modelData): ActionResult
    {
        $this->model=$stock;
        $this->modelData=$modelData;
        return $this->updateAndFinalise(jsonFields:['data','settings']);
    }
}
