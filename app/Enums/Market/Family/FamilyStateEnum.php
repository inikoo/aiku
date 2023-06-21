<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 01:49:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Market\Family;

use App\Enums\EnumHelperTrait;

enum FamilyStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS    = 'in-process';
    case ACTIVE        = 'active';
    case DISCONTINUING = 'discontinuing';
    case DISCONTINUED  = 'discontinued';
}
