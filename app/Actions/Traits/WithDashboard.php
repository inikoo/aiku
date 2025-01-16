<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 09-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Traits;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\DateIntervals\DateIntervalEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;

trait WithDashboard
{
    public function getIntervalOptions(): array
    {
        return collect(DateIntervalEnum::cases())->map(function ($interval) {
            return [
                'label'      => __($interval->name),
                'labelShort' => __($interval->value),
                'value'      => $interval->value
            ];
        })->toArray();
    }

    /**
     * @param string $type = 'basic'
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
     *      'type' => 'percentage'|'progress'|'number',
     *      'value' => float|int,
     *      'label' => string,
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

    // Template for DashboardInterval
    public function getDashboardInterval($model, array $userSettings): array
    {

        $subModel = null;
        $keyCurrency = null;
        if ($model instanceof Group) {
            $subModel = 'organisations';
            $keyCurrency = 'grp';
        } elseif ($model instanceof Organisation) {
            $subModel = 'shops';
            $keyCurrency = 'org';
        }

        $selectedInterval = Arr::get($userSettings, 'selected_interval', 'all');
        $subModelData = $model->{$subModel};
        $currencies = [];
        foreach ($subModelData as $data) {
            $currencies[] = $data->currency->symbol;
        }
        $currenciesSymbol = implode('/', array_unique($currencies));

        $dashboard = [
            'interval_options'  => $this->getIntervalOptions(),
            'settings' => [
                'db_settings'   => auth()->user()->settings,
                'key_currency'  =>  $keyCurrency,
                'options_currency'  => [
                    [
                        'value' => $keyCurrency,
                        'label' => $model->currency->symbol,
                    ],
                    [
                        'value' => $keyCurrency === 'grp' ? 'org' : 'shop',
                        'label' => $currenciesSymbol,
                    ]
                ]
            ],
            'table' => [],
            'widgets' => [
                'column_count'    => 2,
                'components' => []
            ]
        ];

        $selectedCurrency = Arr::get($userSettings, 'selected_currency_in_' . $keyCurrency, 'grp');
        $salesCurrency = 'sales_'.$selectedCurrency.'_currency';
        if ($selectedCurrency === 'shop') {
            $salesCurrency = 'sales';
        }

        $total = [
            'total_sales'    => $model->salesIntervals?->{"sales_{$keyCurrency}_currency_{$selectedInterval}"},
            'total_invoices' => 0,
            'total_refunds'  => 0,
        ];

        $visualData = [
            'sales' => [],
            'invoices' => [],
        ];

        $dashboard['table'] = $subModelData->map(function (Organisation|Shop $subModel) use ($selectedInterval, $model, &$dashboard, $selectedCurrency, $salesCurrency, &$visualData, &$total) {
            $keyCurrency = $dashboard['settings']['key_currency'];
            $currencyCode = $selectedCurrency === $keyCurrency ? $model->currency->code : $subModel->currency->code;
            $responseData = [
                'name'      => $subModel->name,
                'slug'      => $subModel->slug,
                'code'      => $subModel->code,
                'type'      => $subModel->type,
                'currency_code'  => $currencyCode,
                'state'     => $subModel->state,
                'route'     => $subModel->type == ShopTypeEnum::FULFILMENT
            ];

            if ($subModel->salesIntervals !== null) {
                $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                    $subModel->salesIntervals,
                    $salesCurrency,
                    $selectedInterval,
                );
                $visualData['sales_data']['labels'][] = $subModel->code;
                $visualData['sales_data']['currency_codes'][] = $currencyCode;
                $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
            }

            if ($subModel->orderingIntervals !== null) {
                $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
                    $subModel->orderingIntervals,
                    'invoices',
                    $selectedInterval,
                );
                $responseData['interval_percentages']['refunds'] = $this->getIntervalPercentage(
                    $subModel->orderingIntervals,
                    'refunds',
                    $selectedInterval,
                );
                $total['total_invoices'] += $responseData['interval_percentages']['invoices']['amount'];
                $total['total_refunds'] += $responseData['interval_percentages']['refunds']['amount'];
                $visualData['invoices_data']['labels'][] = $subModel->code;
                $visualData['invoices_data']['currency_codes'][] = $currencyCode;
                $visualData['invoices_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['invoices']['amount'];
            }
            return $responseData;
        })->toArray();

        $dashboard['total'] = $total;

        $dashboard['widgets']['components'][] = $this->getWidget(
            data: [
                'status' => $total['total_sales'] < 0 ? 'danger' : '',
                'value' => $total['total_sales'],
                'currency_code' => $model->currency->code,
                'type' => 'currency',
                'description'   => __('Total sales')
            ],
            visual: [
                'type' => 'doughnut',
                'value' => [
                    'labels'  => $visualData['sales_data']['labels'],
                    'currency_codes' => $visualData['sales_data']['currency_codes'],
                    'datasets'    => $visualData['sales_data']['datasets']
                ],
            ]
        );

        $dashboard['widgets']['components'][] = $this->getWidget(
            data: [
                'value' => $total['total_invoices'],
                'type' => 'number',
                'description'   => __('Total invoices')
            ],
            visual: [
                'type' => 'bar',
                'value' => [
                    'labels'  => $visualData['invoices_data']['labels'],
                    'currency_codes' => $visualData['invoices_data']['currency_codes'],
                    'datasets'    => $visualData['invoices_data']['datasets']
                ],
            ]
        );

        return $dashboard;
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

}
