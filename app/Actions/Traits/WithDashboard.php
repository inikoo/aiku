<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 09-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Traits;

use App\Enums\DateIntervals\DateIntervalEnum;
use Illuminate\Support\Arr;

trait WithDashboard
{
    protected ?string $tabDashboardInterval                = null;

    public function getIntervalOptions(): array
    {
        return collect(DateIntervalEnum::cases())->map(function ($interval) {
            return [
                'label'      => __(strtolower(str_replace('_', ' ', $interval->name))),
                'labelShort' => __($interval->value),
                'value'      => $interval->value
            ];
        })->toArray();
    }

    /**
     * @param string $type = 'basic' | 'chart_display'
     * @param int $colSpan = 1
     * @param int $rowSpan = 1
     * @param array $route [
     *      'name' => string,
     *      'params' => array
     * ]
     * @param array $data [
     *      'value' => float|int,
     *      'description' => string,
     *      'status' => 'danger'|'success'|'information',
     *      'type' => 'currency',
     *      'currency_code' => string
     * ]
     * @param array $visual [
     *      'type' => 'pie'|'bar'|'line'|'doughnut',
     *      'value' => [
     *         'labels' => array,
     *         'currency_codes' => array,
     *         'datasets' => [
     *             [
     *                  'label' => string,
     *                  'data' => array,
     *                  'backgroundColor' => array,
     *                  'borderColor' => array
     *              ]
     *          ]
     *       ]
     * ]
     *
     * @return array
     */

    public function getWidget($type = 'basic', $colSpan = 1, $rowSpan = 1, array $route = [], array $data = [], array $visual = []): array
    {
        return [
            'type' => $type,
            'col_span'  => $colSpan,
            'row_span'  => $rowSpan,
            'route' => $route,
            'visual' => $visual,
            'data' => $data
        ];
    }

    public function withTabDashboardInterval(array $tabs): static
    {
        $tab =  $this->get('tab_dashboard_interval', Arr::first($tabs));

        if (!in_array($tab, $tabs)) {
            abort(404);
        }

        $this->tabDashboardInterval = $tab;

        return $this;
    }

    public function calculatePercentageIncrease($thisYear, $lastYear): ?float
    {
        if ($lastYear == 0) {
            return $thisYear > 0 ? null : 0;
        }

        return (($thisYear - $lastYear) / $lastYear) * 100;
    }

    protected function getIntervalPercentage($intervalData, string $prefix, $key): array
    {
        $result = [];

        if ($key == 'all') {
            $result = [
                'amount' => $intervalData->{$prefix . '_all'} ?? null,
            ];
            return $result;
        }

        $result = [
            'amount'     => $intervalData->{$prefix . '_' . $key} ?? null,
            'percentage' => isset($intervalData->{$prefix . '_' . $key}, $intervalData->{$prefix . '_' . $key . '_ly'})
                ? $this->calculatePercentageIncrease(
                    $intervalData->{$prefix . '_' . $key},
                    $intervalData->{$prefix . '_' . $key . '_ly'}
                )
                : null,
            'difference' => isset($intervalData->{$prefix . '_' . $key}, $intervalData->{$prefix . '_' . $key . '_ly'})
                ? $intervalData->{$prefix . '_' . $key} - $intervalData->{$prefix . '_' . $key . '_ly'}
                : null,
        ];

        return $result;
    }

    public function getDateIntervalFilter($interval): string
    {
        $intervals = [
            '1y' => now()->subYear(),
            '1q' => now()->subQuarter(),
            '1m' => now()->subMonth(),
            '1w' => now()->subWeek(),
            '3d' => now()->subDays(3),
            '1d' => now()->subDay(),
            'ytd' => now()->startOfYear(),
            'tdy' => now()->startOfDay(),
            'qtd' => now()->startOfQuarter(),
            'mtd' => now()->startOfMonth(),
            'wtd' => now()->startOfWeek(),
            'lm' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'lw' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'ld' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
        ];

        if (!isset($intervals[$interval])) {
            return '';
        }

        $start = is_array($intervals[$interval]) ? $intervals[$interval][0] : $intervals[$interval];
        $end = is_array($intervals[$interval]) ? $intervals[$interval][1] : now();

        return str_replace('-', '', $start->toDateString()) . '-' . str_replace('-', '', $end->toDateString());
    }

}
