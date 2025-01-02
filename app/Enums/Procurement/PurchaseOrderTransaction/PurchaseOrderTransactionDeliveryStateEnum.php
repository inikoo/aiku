<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 09 May 2023 13:09:10 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrderTransaction;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderTransactionDeliveryStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in_process';
    case CONFIRMED = 'confirmed';
    case READY_TO_SHIP = 'ready_to_ship';
    case DISPATCHED = 'dispatched';
    case RECEIVED = 'received';
    case CHECKED = 'checked';
    case SETTLED = 'settled';
    case NOT_RECEIVED = 'not_received';
    case CANCELLED = 'cancelled';
}
