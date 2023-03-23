<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 02:13:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\Stock;

use App\Enums\EnumHelperTrait;

enum StockQuantityStatusEnum: string
{
    use EnumHelperTrait;

    case EXCESS             = 'excess';
    case IDEAL              = 'ideal';
    case LOW                = 'low';
    case CRITICAL           = 'critical';
    case OUT_OF_STOCK       = 'out-of-stock';
    case ERROR              = 'error';
}
