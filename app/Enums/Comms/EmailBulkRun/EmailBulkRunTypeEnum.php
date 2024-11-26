<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 13:30:40 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\EmailBulkRun;

use App\Enums\EnumHelperTrait;

enum EmailBulkRunTypeEnum: string
{
    use EnumHelperTrait;


    case BASKET_LOW_STOCK = 'basket_low_stock';
    case BASKET_REMINDER_1 = 'basket_reminder_1';
    case BASKET_REMINDER_2 = 'basket_reminder_2';
    case BASKET_REMINDER_3 = 'basket_reminder_3';
    case ABANDONED_CART = 'abandoned_cart';
    case REORDER_REMINDER = 'reorder_reminder';
    case OOS_NOTIFICATION = 'oos_notification';


}
