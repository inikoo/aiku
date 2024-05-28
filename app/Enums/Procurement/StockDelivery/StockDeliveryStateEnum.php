<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\StockDelivery;

use App\Enums\EnumHelperTrait;

enum StockDeliveryStateEnum: string
{
    use EnumHelperTrait;

    case CREATING   = 'creating';
    case DISPATCHED = 'dispatched';
    case RECEIVED   = 'received';
    case CHECKED    = 'checked';
    case SETTLED    = 'settled';


}
