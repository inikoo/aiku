<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Mar 2023 14:21:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Sales\Order;

use App\Enums\EnumHelperTrait;

enum OrderStatusEnum: string
{
    use EnumHelperTrait;

    case PROCESSING = 'processing';
    case DISPATCHED = 'settled-dispatched';
    case FAIL       = 'settled-fail';
    case CANCELLED  = 'settled-cancelled';
}
