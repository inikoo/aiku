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
                'column_count'    => 0,
                'components' => []
            ]
        ];

        $dashboard['table'] = $subModelData->map(function (Organisation|Shop $subModel) use ($selectedInterval, $model, &$dashboard, $userSettings) {
            $keyCurrency = $dashboard['settings']['key_currency'];
            $selectedCurrency = Arr::get($userSettings, 'selected_currency_in_' . $keyCurrency, 'grp');
            $currencyCode = $selectedCurrency === $keyCurrency ? $model->currency->code : $subModel->currency->code;
            $responseData = [
                'name'      => $subModel->name,
                'slug'      => $subModel->slug,
                'code'      => $subModel->code,
                'type'      => $subModel->type,
                'currency_code'  => $currencyCode,
            ];
            if ($subModel->salesIntervals !== null) {
                $dashboard['widgets']['column_count']++;
                $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                    $subModel->salesIntervals,
                    'sales_org_currency',
                    $selectedInterval,
                );
                $amount = $responseData['interval_percentages']['sales']['amount'];
                $dashboard['widgets']['components'][] = [
                    'type' => 'basic',
                    'col_span'  => 1,
                    'row_span'  => 1,
                    'route' => [
                        'name' => 'grp.org.dashboard.show',
                        'params' => [$model->slug]
                    ],
                    'data' => [
                        'value'         =>  $amount,
                        'description'   => __('Sales For ') . $responseData['name'],
                        'status'    => $amount < 0 ? 'danger' : '',
                        'type'      => 'currency',
                        'currency_code' => $currencyCode
                    ]
                ];
            }

            if ($subModel->orderingIntervals !== null) {
                $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
                    $subModel->orderingIntervals,
                    'invoices',
                    $selectedInterval,
                );
            }

            if ($subModel->orderingIntervals !== null) {
                $responseData['interval_percentages']['refunds'] = $this->getIntervalPercentage(
                    $subModel->orderingIntervals,
                    'refunds',
                    $selectedInterval,
                );
            }


            return $responseData;
        })->toArray();

        $dashboard['table']['total'] = [
            'total_sales'    => $subModelData->sum(fn ($data) => $data->salesIntervals?->{"sales_org_currency_" . $selectedInterval} ?? 0),
            'total_invoices' => $subModelData->sum(fn ($data) => $data->orderingIntervals?->{"invoices_{$selectedInterval}"} ?? 0),
            'total_refunds'  => $subModelData->sum(fn ($data) => $data->orderingIntervals?->{"refunds_{$selectedInterval}"} ?? 0),
        ];

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
