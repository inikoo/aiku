<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Mar 2023 14:30:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Dispatching\DeliveryNote;

use App\Enums\EnumHelperTrait;

enum DeliveryNoteStatusEnum: string
{
    use EnumHelperTrait;

    case HANDLING   = 'handling';
    case DISPATCHED = 'settled-dispatched';
    case FAIL       = 'settled-fail';
    case CANCELLED  = 'settled-cancelled';
}
