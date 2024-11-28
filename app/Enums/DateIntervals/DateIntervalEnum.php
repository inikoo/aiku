<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 03:29:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\DateIntervals;

use App\Enums\EnumHelperTrait;
use Illuminate\Support\Arr;

enum DateIntervalEnum: string
{
    use EnumHelperTrait;

    case ALL = 'all';
    case ONE_YEAR = '1y';
    case ONE_QUARTER = '1q';
    case ONE_MONTH = '1m';
    case ONE_WEEK = '1w';
    case THREE_DAYS = '3d';
    case ONE_DAY = '1d';
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
        return Arr::except(array_column(self::cases(), 'value'), ['all']);
    }


    public function wherePeriod($query, $column)
    {
        switch ($this->value) {
            case 'all':
                return $query;
            case '1y':
                return $query->whereBetween($column, [now()->subYear(), now()]);
            case '1q':
                return $query->whereBetween($column, [now()->subQuarter(), now()]);
            case '1m':
                return $query->whereBetween($column, [now()->subMonth(), now()]);
            case '1w':
                return $query->whereBetween($column, [now()->subWeek(), now()]);
            case '3d':
                return $query->whereBetween($column, [now()->subDays(3), now()]);
            case '1d':
                return $query->whereBetween($column, [now()->subDay(), now()]);
            case 'ytd':
                return $query->whereBetween($column, [now()->startOfYear()->startOfDay(), now()]);
            case 'tdy':
                return $query->whereBetween($column, [now()->startOfDay(), now()]);
            case 'qtd':
                return $query->whereBetween($column, [now()->startOfQuarter()->startOfDay(), now()]);
            case 'mtd':
                return $query->whereBetween($column, [now()->startOfMonth()->startOfDay(), now()]);
            case 'wtd':
                return $query->whereBetween($column, [now()->startOfWeek()->startOfDay(), now()]);
            case 'lm':
                return $query->whereBetween($column, [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
            case 'lw':
                return $query->whereBetween($column, [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
            case 'ld':
                return $query->whereBetween($column, [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()]);
        }
    }

    public function whereLastYearPeriod($query, $column)
    {
        switch ($this->value) {
            case '1y':
                return $query->whereBetween($column, [now()->subYears(2), now()->subYear()]);
            case '1q':
                return $query->whereBetween($column, [now()->subQuarter()->subYear(), now()->subYear()]);
            case '1m':
                return $query->whereBetween($column, [now()->subMonth()->subYear(), now()->subYear()]);
            case '1w':
                return $query->whereBetween($column, [now()->subWeek()->subYear(), now()->subYear()]);
            case '3d':
                return $query->whereBetween($column, [now()->subDays(3)->subYear(), now()->subYear()]);
            case '1d':
                return $query->whereBetween($column, [now()->subDay()->subYear(), now()->subYear()]);
            case 'ytd':
                return $query->whereBetween($column, [now()->startOfYear()->subYear(), now()->subYear()]);
            case 'tdy':
                return $query->whereBetween($column, [now()->startOfDay()->subYear(), now()->subYear()]);
            case 'qtd':
                return $query->whereBetween($column, [now()->startOfQuarter()->subYear(), now()->subYear()]);
            case 'mtd':
                return $query->whereBetween($column, [now()->startOfMonth()->subYear(), now()->subYear()]);
            case 'wtd':
                return $query->whereBetween($column, [now()->startOfWeek()->subYear(), now()->subYear()]);
            case 'lm':
                return $query->whereBetween($column, [now()->subMonth()->startOfMonth()->subYear(), now()->subMonth()->endOfMonth()->subYear()]);
            case 'lw':
                return $query->whereBetween($column, [now()->subWeek()->startOfWeek()->subYear(), now()->subWeek()->endOfWeek()->subYear()]);
            case 'ld':
                return $query->whereBetween($column, [now()->subDay()->startOfDay()->subYear(), now()->subDay()->endOfDay()->subYear()]);
            default:
                return null;
        }
    }


}
