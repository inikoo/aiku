<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 03:29:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\DateIntervals;

use App\Enums\EnumHelperTrait;

enum DateIntervalEnum: string
{
    use EnumHelperTrait;

    case ALL = 'all'; // All must be the first value
    case ONE_YEAR = '1y';
    case ONE_QUARTER = '1q';
    case ONE_MONTH = '1m';
    case ONE_WEEK = '1w';
    case THREE_DAYS = '3d';
    // case ONE_DAY = '1d';
    case YEAR_TO_DAY = 'ytd';
    case QUARTER_TO_DAY = 'qtd';
    case MONTH_TO_DAY = 'mtd';
    case WEEK_TO_DAY = 'wtd';
    case TODAY = 'tdy';
    case LAST_MONTH = 'lm';
    case LAST_WEEK = 'lw';
    case YESTERDAY = 'ld';


    public static function lastYearValues(): array
    {
        $intervals = self::values();
        unset($intervals[0]); // This only work if all is the first value
        return $intervals;

    }


    public function wherePeriod($query, $column)
    {
        switch ($this->value) {
            case 'all':
                return $query;
            case '1y':
                return $query->whereBetween($column, [now()->subYear()->startOfDay(), now()->endOfDay()]);
            case '1q':
                return $query->whereBetween($column, [now()->subQuarter()->startOfDay(), now()->endOfDay()]);
            case '1m':
                return $query->whereBetween($column, [now()->subMonth()->startOfDay(), now()->endOfDay()]);
            case '1w':
                return $query->whereBetween($column, [now()->subWeek()->startOfDay(), now()->endOfDay()]);
            case '3d':
                return $query->whereBetween($column, [now()->subDays(3)->startOfDay(), now()->endOfDay()]);
            case '1d':
                return $query->whereBetween($column, [now()->subDay()->startOfDay(), now()->endOfDay()]);
            case 'ytd':
                return $query->whereBetween($column, [now()->startOfYear()->startOfDay(), now()->endOfDay()]);
            case 'tdy':
                return $query->whereBetween($column, [now()->startOfDay(), now()->endOfDay()]);
            case 'qtd':
                return $query->whereBetween($column, [now()->startOfQuarter()->startOfDay(), now()->endOfDay()]);
            case 'mtd':
                return $query->whereBetween($column, [now()->startOfMonth()->startOfDay(), now()->endOfDay()]);
            case 'wtd':
                return $query->whereBetween($column, [now()->startOfWeek()->startOfDay(), now()->endOfDay()]);
            case 'lm':
                return $query->whereBetween($column, [now()->subMonth()->startOfMonth()->startOfDay(), now()->subMonth()->endOfMonth()->endOfDay()]);
            case 'lw':
                return $query->whereBetween($column, [now()->subWeek()->startOfWeek()->startOfDay(), now()->subWeek()->endOfWeek()->endOfDay()]);
            case 'ld':
                return $query->whereBetween($column, [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()]);
        }
    }

    public function whereLastYearPeriod($query, $column)
    {
        switch ($this->value) {
            case '1y':
                return $query->whereBetween($column, [now()->subYears(2)->startOfDay(), now()->subYear()->endOfDay()]);
            case '1q':
                return $query->whereBetween($column, [now()->subQuarter()->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case '1m':
                return $query->whereBetween($column, [now()->subMonth()->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case '1w':
                return $query->whereBetween($column, [now()->subWeek()->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case '3d':
                return $query->whereBetween($column, [now()->subDays(3)->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case '1d':
                return $query->whereBetween($column, [now()->subDay()->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case 'ytd':
                return $query->whereBetween($column, [now()->startOfYear()->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case 'tdy':
                return $query->whereBetween($column, [now()->startOfDay()->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case 'qtd':
                return $query->whereBetween($column, [now()->startOfQuarter()->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case 'mtd':
                return $query->whereBetween($column, [now()->startOfMonth()->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case 'wtd':
                return $query->whereBetween($column, [now()->startOfWeek()->subYear()->startOfDay(), now()->subYear()->endOfDay()]);
            case 'lm':
                return $query->whereBetween($column, [now()->subMonth()->startOfMonth()->subYear()->startOfDay(), now()->subMonth()->endOfMonth()->subYear()->endOfDay()]);
            case 'lw':
                return $query->whereBetween($column, [now()->subWeek()->startOfWeek()->subYear()->startOfDay(), now()->subWeek()->endOfWeek()->subYear()->endOfDay()]);
            case 'ld':
                return $query->whereBetween($column, [now()->subDay()->startOfDay()->subYear()->startOfDay(), now()->subDay()->endOfDay()->subYear()->endOfDay()]);
            default:
                return null;
        }
    }


}
