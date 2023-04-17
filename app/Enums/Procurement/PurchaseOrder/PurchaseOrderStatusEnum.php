<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 17 Apr 2023 17:11:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrder;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderStatusEnum: string
{
    use EnumHelperTrait;

    case PROCESSING = 'processing';
    case PLACED     = 'settled-placed';
    case FAIL       = 'settled-no-received';
    case CANCELLED  = 'settled-cancelled';
}
