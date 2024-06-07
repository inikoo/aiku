<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Mar 2023 13:51:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Dispatching\DeliveryNoteItem;

use App\Enums\EnumHelperTrait;

enum DeliveryNoteItemStatusEnum: string
{
    use EnumHelperTrait;

    case HANDLING                = 'handling';
    case DISPATCHED              = 'settled-dispatched';
    case DISPATCHED_WITH_MISSING = 'settled-with-missing';
    case FAIL                    = 'settled-fail';
    case CANCELLED               = 'settled-cancelled';
}
