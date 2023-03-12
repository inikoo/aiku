<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 03:48:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\DateIntervals;

use App\Enums\EnumHelperTrait;

enum PreviousYearsEnum: string
{
    use EnumHelperTrait;

    case PREVIOUS_YEAR_1 = 'py1';
    case PREVIOUS_YEAR_2 = 'py2';
    case PREVIOUS_YEAR_3 = 'py3';
    case PREVIOUS_YEAR_4 = 'py4';
    case PREVIOUS_YEAR_5 = 'py5';
}
