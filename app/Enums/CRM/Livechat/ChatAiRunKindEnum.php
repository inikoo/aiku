<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\CRM\Livechat;

use App\Enums\EnumHelperTrait;

enum ChatAiRunKindEnum: string
{
    use EnumHelperTrait;

    case HOURLY = 'hourly';
    case EARLY_SWEEP = 'early_sweep';
    case MANUAL = 'manual';
}
