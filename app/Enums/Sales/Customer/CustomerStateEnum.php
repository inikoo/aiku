<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Mar 2023 12:56:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Sales\Customer;

use App\Enums\EnumHelperTrait;

enum CustomerStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in-process';
    case REGISTERED = 'registered';
    case ACTIVE     = 'active';
    case LOSING     = 'losing';

    case LOST = 'lost';
}
