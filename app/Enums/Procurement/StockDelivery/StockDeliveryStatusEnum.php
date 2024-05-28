<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\StockDelivery;

use App\Enums\EnumHelperTrait;

enum StockDeliveryStatusEnum: string
{
    use EnumHelperTrait;

    case PROCESSING = 'processing';
    case PLACED     = 'settled-placed';
    case CANCELLED  = 'settled-cancelled';
}
