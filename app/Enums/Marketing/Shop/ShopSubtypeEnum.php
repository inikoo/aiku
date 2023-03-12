<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 23:56:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Marketing\Shop;

use App\Enums\EnumHelperTrait;

enum ShopSubtypeEnum: string
{
    use EnumHelperTrait;

    case B2B          = 'b2b';
    case B2C          = 'b2c';
    case FULFILMENT   = 'fulfilment';
    case DROPSHIPPING = 'dropshipping';
}
