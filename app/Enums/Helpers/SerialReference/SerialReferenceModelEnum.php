<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 28 Apr 2023 12:27:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Helpers\SerialReference;

use App\Enums\EnumHelperTrait;

enum SerialReferenceModelEnum: string
{
    use EnumHelperTrait;


    case CUSTOMER        = 'customer';
    case ORDER           = 'order';
    case PURCHASE_ORDER  = 'purchase_order';
    case PALLET_DELIVERY = 'pallet_delivery';
}
