<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 02:16:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\Stock;

use App\Enums\EnumHelperTrait;

enum StockStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS        = 'in-process';
    case ACTIVE            = 'active';
    case DISCONTINUING     = 'discontinuing';
    case DISCONTINUED      = 'discontinued';
}
