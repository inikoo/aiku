<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 23:38:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Sales\Order;

use App\Enums\EnumHelperTrait;

enum OrderStateEnum: string
{
    use EnumHelperTrait;

    case SUBMITTED    = 'submitted';
    case IN_WAREHOUSE = 'in-warehouse';
    case PACKED       = 'packed';
    case FINALISED    = 'finalised';
    case DISPATCHED   = 'dispatched';
    case CANCELLED    = 'cancelled';
}
