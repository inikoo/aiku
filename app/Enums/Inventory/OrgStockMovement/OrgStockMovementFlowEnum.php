<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 04:01:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\OrgStockMovement;

use App\Enums\EnumHelperTrait;

enum OrgStockMovementFlowEnum: string
{
    use EnumHelperTrait;


    case IN        = 'in';
    case OUT       = 'out';
    case NO_CHANGE = 'no-change';
}
