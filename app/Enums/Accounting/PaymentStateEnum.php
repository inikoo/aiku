<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Mar 2023 12:56:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Accounting;

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
