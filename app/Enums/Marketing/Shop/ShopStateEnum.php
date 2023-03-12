<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 00:47:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Marketing\Shop;

use App\Enums\EnumHelperTrait;

enum ShopStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS   = 'in-process';
    case OPEN         = 'open';
    case CLOSING_DOWN = 'closing-down';
    case CLOSED       = 'closed';
}
