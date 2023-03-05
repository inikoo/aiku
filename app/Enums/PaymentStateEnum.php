<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Feb 2023 22:10:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums;

enum PaymentStateEnum: string
{
    use EnumHelperTrait;
    case IN_PROCESS = 'in_process';
    case APPROVING  = 'approving';
    case COMPLETED  = 'completed';
    case CANCELLED  = 'cancelled';
    case ERROR      = 'error';
    case DECLINED   = 'declined';
}
