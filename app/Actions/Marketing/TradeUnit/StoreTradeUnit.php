<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:51:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\TradeUnit;

use App\Models\Marketing\TradeUnit;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreTradeUnit
{
    use AsAction;

    public function handle($modelData): TradeUnit
    {
        return TradeUnit::create($modelData);
    }
}
