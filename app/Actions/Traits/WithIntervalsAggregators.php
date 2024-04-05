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
    public function getIntervalStats(
        $queryBase,
        string $statField,
        string $dateField = 'date',
        string $sumField = 'sum_amount'
    ): array {
        $stats=[];
        foreach (DateIntervalEnum::cases() as $period) {
            $query = $queryBase->clone();
            $query = $period->wherePeriod($query, $dateField);

            $stats[$statField . $period->value] = $query->first()[$sumField] ?? 0;
        }
        return $stats;
    }

    public function getLastYearIntervalStats(
        $queryBase,
        string $statField,
        string $dateField = 'date',
        string $sumField = 'sum_amount'
    ): array {
        $stats=[];
        foreach (DateIntervalEnum::cases() as $period) {
            $query = $queryBase->clone();
            if ($query = $period->whereLastYearPeriod($query, $dateField)) {
                $stats[$statField . $period->value . '_ly'] = $query->first()[$sumField] ?? 0;
            }
        }


        return $stats;
    }

    public function processIntervalShopAssetsStats($queryBase): array
    {

        $stats=[];
        $stats=array_merge($stats, $this->getIntervalStats($queryBase, 'shop_amount_', 'date', 'sum_shop'));
        $stats=array_merge($stats, $this->getLastYearIntervalStats($queryBase, 'shop_amount_', 'date', 'sum_shop'));
        $stats=array_merge($stats, $this->getIntervalStats($queryBase, 'group_amount_', 'date', 'sum_group'));
        $stats=array_merge($stats, $this->getLastYearIntervalStats($queryBase, 'group_amount_', 'date', 'sum_group'));
        $stats=array_merge($stats, $this->getIntervalStats($queryBase, 'org_amount_', 'date', 'sum_org'));
        $stats=array_merge($stats, $this->getLastYearIntervalStats($queryBase, 'org_amount_', 'date', 'sum_org'));

        return $stats;
    }

}
