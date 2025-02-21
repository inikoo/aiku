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
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;

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

    protected function getIntervalPercentage($intervalData, string $prefix, $key, $tooltip = '', $currencyCode = 'USD'): array
    {
        $result = [];

        if ($key == 'all') {
            $result = [
                'amount' => $intervalData->{$prefix . '_all'} ?? null,
            ];
            return $result;
        }

        if (!str_starts_with($prefix, 'sales')) {
            $tooltips = "$tooltip" . $intervalData->{$prefix . '_' . $key . '_ly'} ?? 0;
        } else {
            $tooltips = "$tooltip" . Number::currency($intervalData->{$prefix . '_' . $key . '_ly'} ?? 0, $currencyCode);
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
            'tooltip'  =>  $tooltips,
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

    public function setDashboardTableData($parent, $childs, &$dashboard, &$visualData, &$data, $selectedCurrency, $selectedInterval, Closure $route): void
    {
        foreach ($childs as $child) {
            $keyCurrency   = $dashboard['settings']['key_currency'];
            $currencyCode  = $selectedCurrency === $keyCurrency ? $parent->currency->code : $child->currency->code;

            $salesCurrency = 'sales_'.$selectedCurrency.'_currency';

            if ($parent instanceof Organisation) {
                if ($selectedCurrency == 'shop') {
                    $salesCurrency = 'sales';
                }
            }
            $responseData  = array_merge([
                'name'          => $child->name,
                'slug'          => $child->slug,
                'code'          => $child->code,
                'type'          => $child->type,
                'currency_code' => $currencyCode,
            ], $route($child));

            if ($child->salesIntervals !== null) {
                $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                    $child->salesIntervals,
                    $salesCurrency,
                    $selectedInterval,
                    __("Last year sales") . ": ",
                    $currencyCode
                );

                // visual sales
                $visualData['sales_data']['labels'][]              = $child->code ?? $child->name;
                $visualData['sales_data']['currency_codes'][]      = $currencyCode;
                $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
            }

            if ($child->orderingIntervals !== null) {
                $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
                    $child->orderingIntervals,
                    'invoices',
                    $selectedInterval,
                    __("Last year invoices") . ": ",
                );
                $responseData['interval_percentages']['refunds']  = $this->getIntervalPercentage(
                    $child->orderingIntervals,
                    'refunds',
                    $selectedInterval,
                    __("Last year refunds") . ": ",
                );
                // visual invoices and refunds
                $visualData['invoices_data']['labels'][]              = $child->code ?? $child->name;
                $visualData['invoices_data']['currency_codes'][]      = $currencyCode;
                $visualData['invoices_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['invoices']['amount'];

                $visualData['refunds_data']['labels'][]              = $child->code ?? $child->name;
                $visualData['refunds_data']['currency_codes'][]      = $currencyCode;
                $visualData['refunds_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['refunds']['amount'];
            };

            $visualData['name'][] = $child->name;

            $data[] = $responseData;
        }

        $parentSalesType = 'grp';

        if ($parent instanceof Organisation) {
            $parentSalesType = 'org';
        }

        $suffixLy = $selectedInterval . '_ly';

        // total
        $totalSales = $childs->sum(function ($child) use ($selectedInterval, $parentSalesType) {
            return $child->salesIntervals->{"sales_$parentSalesType" . "_currency_$selectedInterval"} ?? 0;
        }) ?? 0;
        $totalInvoices = $childs->sum(function ($child) use ($selectedInterval) {
            return $child->orderingIntervals->{"invoices_$selectedInterval"} ?? 0;
        }) ?? 0;

        $totalRefunds = $childs->sum(function ($child) use ($selectedInterval) {
            return $child->orderingIntervals->{"refunds_$selectedInterval"} ?? 0;
        }) ?? 0;


        // last year
        $totalSalesLy = $childs->sum(function ($child) use ($parentSalesType, $suffixLy) {
            return $child->salesIntervals->{"sales_$parentSalesType" . "_currency_$suffixLy"} ?? 0;
        }) ?? 0;

        $totalInvoicesLy = $childs->sum(function ($child) use ($suffixLy) {
            return $child->orderingIntervals->{"invoices_$suffixLy"} ?? 0;
        }) ?? 0;

        $totalRefundsLy = $childs->sum(function ($child) use ($suffixLy) {
            return $child->orderingIntervals->{"refunds_$suffixLy"} ?? 0;
        }) ?? 0;

        $totalSalesPercentage = $this->calculatePercentageIncrease(
            $totalSales,
            $totalSalesLy
        );

        $totalInvoicePercentage = $this->calculatePercentageIncrease(
            $totalInvoices,
            $totalInvoicesLy
        );

        $totalRefundsPercentage = $this->calculatePercentageIncrease(
            $totalRefunds,
            $totalRefundsLy
        );

        $dashboard['total'] = [
            'total_sales'    => $totalSales,
            'total_sales_percentages' => $totalSalesPercentage,
            'total_invoices' => $totalInvoices,
            'total_invoices_percentages' => $totalInvoicePercentage,
            'total_refunds'  => $totalRefunds,
            'total_refunds_percentages' => $totalRefundsPercentage,
        ];

        $dashboard['total_tooltip'] = [
            'total_sales'    => __("Last year sales") . ": " . Number::currency($totalSalesLy, $parent->currency->code),
            'total_invoices' => __("Last year invoices") . ": " . $totalInvoicesLy,
            'total_refunds'  => __("Last year refunds") . ": " . $totalRefundsLy,
        ];

    }


    public function sortVisualDataset(...$data): array
    {
        $combined = array_map(null, ...$data);

        usort($combined, function ($a, $b) {
            return floatval($b[2]) <=> floatval($a[2]);
        });

        return $combined;
    }

    // public function setDashboardVisualData(&$dashboard): void
    // {

    //     $total = $dashboard['total'];

    // }

}
