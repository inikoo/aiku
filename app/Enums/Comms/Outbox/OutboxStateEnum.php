<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\Outbox;

use App\Enums\EnumHelperTrait;

enum OutboxStateEnum: string
{
    use EnumHelperTrait;
    case IN_PROCESS = 'in_process';
    case ACTIVE     = 'active';
    case SUSPENDED  = 'suspended';
}
