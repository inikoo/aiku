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
    case LAST_MONTH = 'lm';
    case LAST_WEEK = 'lw';


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
                return $query->whereDate($column, '>', now()->subYear());
            case '1q':
                return $query->whereDate($column, '>', now()->subQuarter());
            case '1m':
                return $query->whereDate($column, '>', now()->subMonth());
            case '1w':
                return $query->whereDate($column, '>', now()->subWeek());
            case '3d':
                return $query->whereDate($column, '>', now()->subDays(3));
            case '1d':
                return $query->whereDate($column, '>', now()->subDay());
            case 'ytd':
                return $query->whereYear($column, now()->year);
            case 'qtd':
                return $query->whereBetween($column, [now()->startOfQuarter(), now()]);
            case 'mtd':
                return $query->whereMonth($column, now()->month);
            case 'wtd':
                return $query->whereBetween($column, [now()->startOfWeek(), now()]);
            case 'lm':
                return $query->whereMonth($column, now()->subMonth()->month);
            case 'lw':
                return $query->whereBetween($column, [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
        }
    }

    public function whereLastYearPeriod($query, $column)
    {
        switch ($this->value) {
            case '1y':
                return $query->whereDate($column, '>', now()->subYear());
            case '1q':
                return $query->whereDate($column, '>', now()->subQuarter());
            case '1m':
                return $query->whereDate($column, '>', now()->subMonth());
            case '1w':
                return $query->whereDate($column, '>', now()->subWeek());
            case '3d':
                return $query->whereDate($column, '>', now()->subDays(3));
            case '1d':
                return $query->whereDate($column, '>', now()->subDay());
            case 'ytd':
                return $query->whereYear($column, now()->year);
            case 'qtd':
                return $query->whereBetween($column, [now()->startOfQuarter(), now()]);
            case 'mtd':
                return $query->whereMonth($column, now()->month);
            case 'wtd':
                return $query->whereBetween($column, [now()->startOfWeek(), now()]);
            case 'lm':
                return $query->whereMonth($column, now()->subMonth()->month);
            case 'lw':
                return $query->whereBetween($column, [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
            default:
                return null;
        }
    }


}
