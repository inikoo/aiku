<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 23:47:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Marketing\Shop;

use App\Enums\EnumHelperTrait;

enum ShopTypeEnum: string
{
    use EnumHelperTrait;

    case SHOP             = 'shop';
    case FULFILMENT_HOUSE = 'fulfilment-house';
    case AGENT            = 'agent';
}
