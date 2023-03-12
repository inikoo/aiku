<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Mailroom\Outbox;

use App\Enums\EnumHelperTrait;

enum OutboxStateEnum: string
{
    use EnumHelperTrait;
    case IN_PROCESS = 'in_process';
    case ACTIVE     = 'active';
    case SUSPENDED  = 'suspended';
}
