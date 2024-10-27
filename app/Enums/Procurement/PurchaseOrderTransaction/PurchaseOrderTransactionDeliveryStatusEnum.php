<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 09 May 2023 13:09:10 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrderTransaction;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderTransactionDeliveryStatusEnum: string
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
    case CANCELLED = 'cancelled';
}
