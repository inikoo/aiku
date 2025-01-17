<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Dec 2024 00:41:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dashboards;

use App\Actions\OrgAction;
use App\Actions\Traits\WithDashboard;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class ShowGroupDashboard extends OrgAction
{
    use WithDashboard;
    public function handle(Group $group): Response
    {

        $userSettings = auth()->user()->settings;
        return Inertia::render(
            'Dashboard/GrpDashboard',
            [
                'breadcrumbs'       => $this->getBreadcrumbs(__('Dashboard')),
                'dashboard_stats' => $this->getDashboardInterval($group, $userSettings),
            ]
        );
    }
    public function getDashboardInterval(Group $group, array $userSettings): array
    {
        $selectedInterval = Arr::get($userSettings, 'selected_interval', 'all');
        $organisations = $group->organisations;
        $orgCurrencies = [];
        foreach ($organisations as $organisation) {
            $orgCurrencies[] = $organisation->currency->symbol;
        }
        $orgCurrenciesSymbol = implode('/', array_unique($orgCurrencies));

        $dashboard = [
            'interval_options'  => $this->getIntervalOptions(),
            'settings' => [
                'db_settings'   => $userSettings,
                'key_currency'  =>  'grp',
                'options_currency'  => [
                    [
                        'value' => 'grp',
                        'label' => $group->currency->symbol,
                    ],
                    [
                        'value' => 'org',
                        'label' => $orgCurrenciesSymbol,
                    ]
                ]
            ],
            'table' => [],
            'widgets' => [
                'column_count'    => 4,
                'components' => []
            ]
        ];

        $selectedCurrency = Arr::get($userSettings, 'selected_currency_in_grp', 'grp');
        $total = [
            'total_sales'    => $group->organisations->sum(fn ($organisation) => $organisation->salesIntervals->{"sales_grp_currency_$selectedInterval"} ?? 0),
            'total_invoices' => 0,
            'total_refunds'  => 0,
        ];

        $visualData = [
            'sales' => [],
            'invoices' => [],
        ];

        $dashboard['table'] = $organisations->map(function (Organisation $organisation) use ($selectedInterval, $group, &$dashboard, $selectedCurrency, &$visualData, &$total) {
            $keyCurrency = $dashboard['settings']['key_currency'];
            $currencyCode = $selectedCurrency === $keyCurrency ? $group->currency->code : $organisation->currency->code;
            $salesCurrency = 'sales_'.$selectedCurrency.'_currency';
            $responseData = [
                'name'      => $organisation->name,
                'slug'      => $organisation->slug,
                'code'      => $organisation->code,
                'type'      => $organisation->type,
                'currency_code'  => $currencyCode,
                'route'     => [
                    'name'       => 'grp.org.dashboard.show',
                    'parameters' => [
                        'organisation' => $organisation->slug,
                    ]
                ]
            ];

            if ($organisation->salesIntervals !== null) {
                $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                    $organisation->salesIntervals,
                    $salesCurrency,
                    $selectedInterval,
                );
                $visualData['sales_data']['labels'][] = $organisation->code;
                $visualData['sales_data']['currency_codes'][] = $currencyCode;
                $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
            }

            if ($organisation->orderingIntervals !== null) {
                $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
                    $organisation->orderingIntervals,
                    'invoices',
                    $selectedInterval,
                );
                $responseData['interval_percentages']['refunds'] = $this->getIntervalPercentage(
                    $organisation->orderingIntervals,
                    'refunds',
                    $selectedInterval,
                );
                $total['total_invoices'] += $responseData['interval_percentages']['invoices']['amount'];
                $total['total_refunds'] += $responseData['interval_percentages']['refunds']['amount'];
                $visualData['invoices_data']['labels'][] = $organisation->code;
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
                'currency_code' => $group->currency->code,
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

    public function asController(): Response
    {
        $group = group();
        $this->initialisationFromGroup($group, []);
        return $this->handle($group);
    }

    public function getBreadcrumbs($label = null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-tachometer-alt-fast',
                    'label' => $label,
                    'route' => [
                        'name' => 'grp.dashboard.show'
                    ]
                ]

            ],

        ];
    }
}
