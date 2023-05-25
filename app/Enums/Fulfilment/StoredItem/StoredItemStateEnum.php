<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 03:02:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\StoredItem;

use App\Enums\EnumHelperTrait;

enum StoredItemStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS       = 'in-process';
    case RECEIVED         = 'received';
    case BOOKED_IN        = 'booked-in';
    case SETTLED          = 'settled';
}
