<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 02:46:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Accounting\Payment;

use App\Enums\EnumHelperTrait;

enum PaymentStatusEnum: string
{
    use EnumHelperTrait;
    case IN_PROCESS = 'in-process';
    case SUCCESS    = 'success';
    case FAIL       = 'fail';
}
