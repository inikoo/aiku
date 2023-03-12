<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 23:45:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Accounting\Payment;

use App\Enums\EnumHelperTrait;

enum PaymentStateEnum: string
{
    use EnumHelperTrait;
    case IN_PROCESS = 'in-process';
    case APPROVING  = 'approving';
    case COMPLETED  = 'completed';
    case CANCELLED  = 'cancelled';
    case ERROR      = 'error';
    case DECLINED   = 'declined';
}
