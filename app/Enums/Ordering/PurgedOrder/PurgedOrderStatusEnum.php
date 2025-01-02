<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Nov 2024 17:07:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Ordering\PurgedOrder;

use App\Enums\EnumHelperTrait;

enum PurgedOrderStatusEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in_process';
    case PURGED = 'purged';
    case EXCULPATED = 'exculpated'; // Inactive order when purge created, that get activated before order purged
    case CANCELLED = 'cancelled';
    case ERROR = 'error';
}
