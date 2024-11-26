<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 24 Nov 2024 10:16:34 Central Indonesia Time, Kuta, Bali, Indonesia
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
