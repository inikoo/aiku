<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 09 May 2023 13:09:10 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrderTransaction;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderTransactionStatusEnum: string
{
    use EnumHelperTrait;

    case PROCESSING = 'processing';
    case PLACED     = 'settled-placed';
    case FAIL       = 'settled-no-received';
    case CANCELLED  = 'settled-cancelled';
}
