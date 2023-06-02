<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 02:16:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\Stock;

use App\Enums\EnumHelperTrait;

enum LostAndFoundStockStateEnum: string
{
    use EnumHelperTrait;

    case LOST  = 'lost';
    case FOUND = 'found';
}
