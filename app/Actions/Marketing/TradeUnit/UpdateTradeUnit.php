<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:58:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\TradeUnit;

use App\Actions\UpdateModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Marketing\TradeUnit;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTradeUnit extends UpdateModelAction
{
    use AsAction;

    public function handle(TradeUnit $tradeUnit, array $modelData): ActionResult
    {

        $this->model=$tradeUnit;
        $this->modelData=$modelData;
        return $this->updateAndFinalise(jsonFields:['data','dimensions']);

    }
}
