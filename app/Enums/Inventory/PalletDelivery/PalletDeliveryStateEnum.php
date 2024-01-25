<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 03:19:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\PalletDelivery;

use App\Enums\EnumHelperTrait;

enum PalletDeliveryStateEnum: string
{
    use EnumHelperTrait;

    case IN  = 'in';
    case OUT = 'out';
}
