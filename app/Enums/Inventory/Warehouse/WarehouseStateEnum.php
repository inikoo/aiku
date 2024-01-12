<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Jan 2024 17:05:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\Warehouse;

use App\Enums\EnumHelperTrait;

enum WarehouseStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS   = 'in-process';
    case OPEN         = 'open';
    case CLOSING_DOWN = 'closing-down';
    case CLOSED       = 'closed';
}
