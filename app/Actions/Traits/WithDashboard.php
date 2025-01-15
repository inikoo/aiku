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
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;

trait WithDashboard
{
    protected string $currencyCode = 'usd';
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
     * data = [
     *      'value' => number,
     *      'description' => 'string',
     *      'status' => (string) 'danger' | 'success' | 'information,
     *      'type' => 'currency',
     *      'currency_code' => 'string'
     * ]
     *
     * route = [
     *      'name' => 'string',
     *      'params' => array
     * ]
     *
     * visual = [
     *      'type' => 'percentage' | 'progress' | 'number',
     *      'value' => number,
     *      'label' => 'string',
     * ]
     *
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

    public function getDashboardInterval(Group|Organisation $model, array $userSettings): array
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
                'column_count'    => 4,
                'components' => []
            ]
        ];

        $selectedCurrency = Arr::get($userSettings, 'selected_currency_in_' . $keyCurrency, 'grp');
        $salesCurrency = 'sales_'.$selectedCurrency.'_currency';
        if ($selectedCurrency === 'shop') {
            $salesCurrency = 'sales';
        }

        $total = [
            'total_sales'    => 0,
            'total_invoices' => 0,
            'total_refunds'  => 0,
        ];
        $dashboard['table'] = $subModelData->map(function (Organisation|Shop $subModel) use ($selectedInterval, $model, &$dashboard, $selectedCurrency, $salesCurrency, &$total) {
            $keyCurrency = $dashboard['settings']['key_currency'];
            $currencyCode = $selectedCurrency === $keyCurrency ? $model->currency->code : $subModel->currency->code;
            $this->currencyCode = $currencyCode;
            $responseData = [
                'name'      => $subModel->name,
                'slug'      => $subModel->slug,
                'code'      => $subModel->code,
                'type'      => $subModel->type,
                'currency_code'  => $currencyCode,
            ];
            if ($subModel->salesIntervals !== null) {
                $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                    $subModel->salesIntervals,
                    $salesCurrency,
                    $selectedInterval,
                );
                $total['total_sales'] += $responseData['interval_percentages']['sales']['amount'];
                // $dashboard['widgets']['components'][] = $this->getWidget(
                //     route: [
                //         'name' => 'grp.org.dashboard.show',
                //         'params' => [$model->slug]
                //     ],
                //     data: [
                //         'value'         => $amount,
                //         'description'   => __('Sales For ') . $responseData['name'],
                //         'status'        => $amount < 0 ? 'danger' : '',
                //         'type'          => 'currency',
                //         'currency_code' => $currencyCode
                //     ],
                // );
            }

            if ($subModel->orderingIntervals !== null) {
                $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
                    $subModel->orderingIntervals,
                    'invoices',
                    $selectedInterval,
                );
                $total['total_invoices'] += $responseData['interval_percentages']['invoices']['amount'];
            }

            if ($subModel->orderingIntervals !== null) {
                $responseData['interval_percentages']['refunds'] = $this->getIntervalPercentage(
                    $subModel->orderingIntervals,
                    'refunds',
                    $selectedInterval,
                );
                $total['total_refunds'] = $responseData['interval_percentages']['invoices']['amount'];
            }
            return $responseData;
        })->toArray();

        $dashboard['total'] = $total;

        $dashboard['widgets']['components'][] = $this->getWidget(
            data: [
                // 'status' => 'success',
                'value' => $total['total_sales'],
                'currency_code' => $this->currencyCode,
                'type' => 'currency',
                'description'   => __('Total sales')
            ],
            visual: [
                'type' => 'doughnut',
                'value' => [
                    'labels'  => ['AWA', 'ES', 'Aroma'],
                    'datasets'    => [
                        [
                            'data'    => [600, 297000, 27145],
                        ]
                    ]
                ],
                // 'label' => __('Total Sales')
            ]
        );

        $dashboard['widgets']['components'][] = $this->getWidget(
            data: [
                // 'status' => 'danger',
                'value' => $total['total_invoices'],
                'currency_code' => $this->currencyCode,
                'type' => 'currency',
                'description'   => __('Total invoices')
            ],
            visual: [
                'type' => 'bar',
                'value' => [
                    'labels'  => ['AWA', 'ES', 'Aroma'],
                    'datasets'    => [
                        [
                            'data'    => [125, 403, 78],
                        ]
                    ]
                ],
                // 'label' => __('Total Invoice')
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
