<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 04:02:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\StockMovement;

use App\Enums\EnumHelperTrait;

enum StockMovementTypeEnum: string
{
    use EnumHelperTrait;

    case PURCHASE            = 'purchase';
    case FULFILMENT_DELIVERY = 'fulfilment-delivery';
    case DISPATCH_RETURN     = 'dispatch-return';
    case RETURN_PICKED       = 'return-picked';
    case FULFILMENT_RETURN   = 'fulfilment-return';
    case PICKED              = 'picked';
    case LOCATION_TRANSFER   = 'location-transfer';
    case FOUND               = 'found';
    case CONSUMPTION         = 'consumption';
    case WRITE_OFF           = 'write-off';
}
