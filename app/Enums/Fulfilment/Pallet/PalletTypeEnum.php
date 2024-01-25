<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:23:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\Pallet;

use App\Enums\EnumHelperTrait;

enum PalletTypeEnum: string
{
    use EnumHelperTrait;

    case PALLET   = 'pallet';
    case BOX      = 'box';
    case OVERSIZE = 'oversize';
}
