<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 23:47:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Auth;

use App\Enums\EnumHelperTrait;

enum GuestTypeEnum: string
{
    use EnumHelperTrait;

    case type             = 'type';
    case FULFILMENT_HOUSE = 'fulfilment-house';
    case AGENT            = 'agent';
}
