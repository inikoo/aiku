<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Apr 2024 11:40:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Enums\DateIntervals\DateIntervalEnum;

trait WithIntervalsAggregators
{
    public function getIntervalsData(array $stats, $queryBase, $statField, $dateField = 'date', $sumField = 'sum_aggregate'): array
    {
        $stats = array_merge($stats, $this->getIntervalStats($queryBase, $statField, $dateField, $sumField));

        return array_merge($stats, $this->getLastYearIntervalStats($queryBase, $statField, $dateField, $sumField));
    }


    public function getIntervalStats(
        $queryBase,
        string $statField,
        string $dateField = 'date',
        string $sumField = 'sum_aggregate'
    ): array {
        $stats = [];
        foreach (DateIntervalEnum::cases() as $period) {
            $query = $queryBase->clone();
            $query = $period->wherePeriod($query, $dateField);

            $res                              = $query->first();
            $stats[$statField.$period->value] = $res->{$sumField} ?? 0;
        }

        return $stats;
    }

    public function getLastYearIntervalStats(
        $queryBase,
        string $statField,
        string $dateField = 'date',
        string $sumField = 'sum_aggregate'
    ): array {
        $stats = [];
        foreach (DateIntervalEnum::cases() as $period) {
            $query = $queryBase->clone();
            if ($query = $period->whereLastYearPeriod($query, $dateField)) {
                $res                                    = $query->first();
                $stats[$statField.$period->value.'_ly'] = $res->{$sumField} ?? 0;
            }
        }


        return $stats;
    }


}
