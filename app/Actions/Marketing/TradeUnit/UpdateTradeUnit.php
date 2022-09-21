<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:58:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\TradeUnit;

use App\Actions\WithActionUpdate;
use App\Models\Marketing\TradeUnit;

class UpdateTradeUnit
{
    use WithActionUpdate;

    public function handle(TradeUnit $tradeUnit, array $modelData): TradeUnit
    {
        return $this->update($tradeUnit, $modelData, ['data', 'dimensions']);
    }
}
