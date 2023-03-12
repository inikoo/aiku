<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 03:29:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\DateIntervals;

use App\Enums\EnumHelperTrait;
use Illuminate\Support\Arr;

enum PeriodsEnum: string
{
    use EnumHelperTrait;

    case ALL            = 'all';
    case ONE_YEAR       = '1y';
    case ONE_QUARTER    = '1q';
    case ONE_MONTH      = '1m';
    case ONE_WEEK       = '1w';
    case YEAR_TO_DAY    = 'ytd';
    case QUARTER_TO_DAY = 'qtd';
    case MONTH_TO_DAY   = 'mtd';
    case WEEK_TO_DAY    = 'wtd';
    case LAST_MONTH     = 'lm';
    case LAST_WEEK      = 'lw';
    case YESTERDAY      = 'yda';
    case TODAY          = 'tdy';


    public static function lastYearValues(): array
    {
        return Arr::except(array_column(self::cases(), 'value'), ['all']);
    }
}
