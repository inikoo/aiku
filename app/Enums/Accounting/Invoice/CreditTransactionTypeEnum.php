<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Aug 2024 10:23:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Accounting\Invoice;

use App\Enums\EnumHelperTrait;

enum CreditTransactionTypeEnum: string
{
    use EnumHelperTrait;

    case TOP_UP             = 'top_up';
    case PAYMENT            = 'payment';
    case ADJUST             = 'adjust';
    case CANCEL             = 'cancel';
    case RETURN             = 'return';
    case PAY_RETURN         = 'pay_return';
    case ADD_FUNDS_OTHER    = 'add_funds_other';
    case COMPENSATION       = 'compensation';
    case TRANSFER_IN        = 'transfer_in';
    case MONEY_BACK         = 'money_back';
    case TRANSFER_OUT       = 'transfer_out';
    case REMOVE_FUNDS_OTHER = 'remove_funds_other';


}
