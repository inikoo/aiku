<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 17 Apr 2023 17:11:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrder;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderDeliveryStatusEnum: string
{
    use EnumHelperTrait;

    case PROCESSING = 'processing';
    case CONFIRMED = 'confirmed';
    case READY_TO_SHIP = 'ready-to-ship';
    case DISPATCHED = 'dispatched';
    case RECEIVED = 'received';
    case CHECKED = 'checked';
    case SETTLED = 'settled';
    case NOT_RECEIVED = 'not-received';
    case CANCELLED = 'settled-cancelled';
}
