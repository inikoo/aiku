<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:23:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\StoredItem;

use App\Enums\EnumHelperTrait;

enum StoredItemMovementTypeEnum: string
{
    use EnumHelperTrait;

    case RECEIVED    = 'received';
    case PICKED      = 'picked';
    case MOVED       = 'moved';
    case DAMAGED     = 'damaged';
}
