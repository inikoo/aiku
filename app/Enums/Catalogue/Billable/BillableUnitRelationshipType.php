<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 03:29:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Catalogue\Billable;

use App\Enums\EnumHelperTrait;

enum BillableUnitRelationshipType: string
{
    use EnumHelperTrait;

    case SINGLE                           = 'single';
    case MULTIPLE                         = 'multiple';
    case MULTIPLE_REPACKED                = 'multiple_repacked';
    case MIX                              = 'mix';
    case TIME_INTERVAL                    = 'time_interval';
}
