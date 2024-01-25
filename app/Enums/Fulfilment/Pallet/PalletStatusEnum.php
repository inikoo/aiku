<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\Pallet;

use App\Enums\EnumHelperTrait;

enum PalletStatusEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in-process';
    case STORING    = 'storing';
    case DAMAGED    = 'damaged';
    case LOST       = 'lost';
    case RETURNED   = 'returned';
}
