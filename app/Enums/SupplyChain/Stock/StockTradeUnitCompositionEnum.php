<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 11:02:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\Stock;

use App\Enums\EnumHelperTrait;

enum StockTradeUnitCompositionEnum: string
{
    use EnumHelperTrait;

    case MATCH                 = 'match';
    case MULTIPLE              = 'multiple';
    case MULTIPLE_REPACKED     = 'multiple_repacked';
    case MIX                   = 'mix';
}
