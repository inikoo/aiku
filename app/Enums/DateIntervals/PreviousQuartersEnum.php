<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 03:52:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\DateIntervals;

use App\Enums\EnumHelperTrait;

enum PreviousQuartersEnum: string
{
    use EnumHelperTrait;

    case PREVIOUS_QUARTER_1 = 'pq1';
    case PREVIOUS_QUARTER_2 = 'pq2';
    case PREVIOUS_QUARTER_3 = 'pq3';
    case PREVIOUS_QUARTER_4 = 'pq4';
    case PREVIOUS_QUARTER_5 = 'pq5';

    public function wherePeriod($query, $column)
    {
        switch ($this->value) {
            case 'pq1':
                return $query->whereBetween($column, [now()->subQuarters(1)->startOfQuarter(), now()->subQuarters(1)->endOfQuarter()]);
            case 'pq2':
                return $query->whereBetween($column, [now()->subQuarters(2)->startOfQuarter(), now()->subQuarters(2)->endOfQuarter()]);
            case 'pq3':
                return $query->whereBetween($column, [now()->subQuarters(3)->startOfQuarter(), now()->subQuarters(3)->endOfQuarter()]);
            case 'pq4':
                return $query->whereBetween($column, [now()->subQuarters(4)->startOfQuarter(), now()->subQuarters(4)->endOfQuarter()]);
            case 'pq5':
                return $query->whereBetween($column, [now()->subQuarters(5)->startOfQuarter(), now()->subQuarters(5)->endOfQuarter()]);
            default:
                return $query;
        }
    }
}
