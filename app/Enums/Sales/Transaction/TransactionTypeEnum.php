<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Mar 2023 13:59:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Sales\Transaction;

use App\Enums\EnumHelperTrait;

enum TransactionTypeEnum: string
{
    use EnumHelperTrait;

    case ORDER  = 'order';
    case REFUND = 'refund';
}
