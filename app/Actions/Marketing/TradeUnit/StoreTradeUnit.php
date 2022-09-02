<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:51:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\TradeUnit;

use App\Actions\StoreModelAction;
use App\Models\Organisations\Organisation;
use App\Models\Utils\ActionResult;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreTradeUnit extends StoreModelAction
{
    use AsAction;

    public function handle(Organisation $organisation,$modelData): ActionResult
    {
        $tradeUnit = $organisation->tradeUnits()->create($modelData);
        return $this->finalise($tradeUnit);
    }
}
