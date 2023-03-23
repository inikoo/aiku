<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Mar 2023 13:51:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Sales\Transaction;

use App\Enums\EnumHelperTrait;

enum TransactionStatusEnum: string
{
    use EnumHelperTrait;


    case PROCESSING              = 'processing';
    case DISPATCHED              = 'settled-dispatched';
    case DISPATCHED_WITH_MISSING = 'settled-with-missing';
    case FAIL                    = 'settled-fail';
    case CANCELLED               = 'settled-cancelled';
}
