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

    public function wherePeriod($query, $column)
    {
        switch ($this->value) {
            case 'py1':
                return $query->whereBetween($column, [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()]);
            case 'py2':
                return $query->whereBetween($column, [now()->subYears(2)->startOfYear(), now()->subYears(2)->endOfYear()]);
            case 'py3':
                return $query->whereBetween($column, [now()->subYears(3)->startOfYear(), now()->subYears(3)->endOfYear()]);
            case 'py4':
                return $query->whereBetween($column, [now()->subYears(4)->startOfYear(), now()->subYears(4)->endOfYear()]);
            case 'py5':
                return $query->whereBetween($column, [now()->subYears(5)->startOfYear(), now()->subYears(5)->endOfYear()]);
            default:
                return $query;
        }
    }
}
