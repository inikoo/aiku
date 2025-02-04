<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Dec 2024 00:41:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dashboards;

use App\Actions\OrgAction;
use App\Actions\Traits\DashboardIntervalTabsEnum;
use App\Actions\Traits\WithDashboard;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

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
        $organisations = $group->organisations()->where('type', '!=', OrganisationTypeEnum::AGENT->value)->get();
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
           /*  'tabs' => [
                'current' => $this->tabDashboardInterval,
                'navigation'  => DashboardIntervalTabsEnum::navigation()
            ], */
            'table' => [
                [
                    'tab_label' => __('sales'),
                    'tab_slug'  => 'sales',
                    'tab_icon'  => 'fas fa-chart-line',
                    'type'     => 'table',
                    'data' => null
                ],
                [
                    'tab_label' => __('shops'),
                    'tab_slug'  => 'shops',
                    'tab_icon'  => 'fal fa-shopping-cart',
                    'type'     => 'table',
                    'data' => null
                ]
            ],
            'widgets' => [
                'column_count'    => 4,
                'components' => []
            ]
        ];


        $selectedCurrency = Arr::get($userSettings, 'selected_currency_in_grp', 'grp');

        $total = [
            'total_sales'    => 0,
            'total_invoices' => 0,
            'total_refunds'  => 0,
        ];

        $visualData = [];

        if ($this->tabDashboardInterval == DashboardIntervalTabsEnum::SALES->value) {

            $total['total_sales'] = $organisations->sum(fn ($organisation) => $organisation->salesIntervals->{"sales_grp_currency_$selectedInterval"} ?? 0);

            $dashboard['table'][0]['data'] = $organisations->map(function (Organisation $organisation) use ($selectedInterval, $group, &$dashboard, $selectedCurrency, &$visualData, &$total) {
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

                    $visualData['refunds_data']['labels'][] = $organisation->code;
                    $visualData['refunds_data']['currency_codes'][] = $currencyCode;
                    $visualData['refunds_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['refunds']['amount'];
                }
                return $responseData;
            })->toArray();

            $dashboard['total'] = $total;
        } elseif ($this->tabDashboardInterval == DashboardIntervalTabsEnum::SHOPS->value) {
            $shops = $group->shops->whereIn('organisation_id', $organisations->pluck('id')->toArray());
            $total['total_sales'] = $shops->sum(fn ($shop) => $shop->salesIntervals->{"sales_grp_currency_$selectedInterval"} ?? 0);

            $dashboard['table'][1]['data'] = $shops->map(function (Shop $shop) use ($selectedInterval, $group, &$dashboard, $selectedCurrency, &$visualData, &$total) {
                $keyCurrency = $dashboard['settings']['key_currency'];
                $currencyCode = $selectedCurrency === $keyCurrency ? $group->currency->code : $shop->organisation->currency->code;
                $salesCurrency = 'sales_'.$selectedCurrency.'_currency';
                $responseData = [
                    'name'      => $shop->name,
                    'slug'      => $shop->slug,
                    'code'      => $shop->code,
                    'type'      => $shop->type,
                    'currency_code'  => $currencyCode,
                    'route'     => [
                        'name'       => 'grp.org.dashboard.show',
                        'parameters' => [
                            'shop' => $shop->slug,
                        ]
                    ]
                ];


                if ($shop->salesIntervals !== null) {
                    // data sales
                    $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                        $shop->salesIntervals,
                        $salesCurrency,
                        $selectedInterval,
                    );
                    // visual sales
                    $visualData['sales_data']['labels'][] = $shop->code;
                    $visualData['sales_data']['currency_codes'][] = $currencyCode;
                    $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
                }

                if ($shop->orderingIntervals !== null) {
                    // data invoices
                    $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
                        $shop->orderingIntervals,
                        'invoices',
                        $selectedInterval,
                    );
                    // data refunds
                    $responseData['interval_percentages']['refunds'] = $this->getIntervalPercentage(
                        $shop->orderingIntervals,
                        'refunds',
                        $selectedInterval,
                    );
                    $total['total_invoices'] += $responseData['interval_percentages']['invoices']['amount'];
                    $total['total_refunds'] += $responseData['interval_percentages']['refunds']['amount'];

                    // visual data
                    $visualData['invoices_data']['labels'][] = $shop->code;
                    $visualData['invoices_data']['currency_codes'][] = $currencyCode;
                    $visualData['invoices_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['invoices']['amount'];

                    $visualData['refunds_data']['labels'][] = $shop->code;
                    $visualData['refunds_data']['currency_codes'][] = $currencyCode;
                    $visualData['refunds_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['refunds']['amount'];
                }
                return $responseData;
            })->toArray();


            $dashboard['total'] = $total;
        }

        $dashboard['widgets']['components'][] = $this->getWidget(
            type: 'chart_display',
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
            type: 'chart_display',
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
                    /* 'datasets'    => $visualData['invoices_data']['datasets'] */
                    //TODO: new datasets
                    'datasets'    => [
                        [
                            'label' => __('Invoices'),
                            'data'  => $visualData['refunds_data']['datasets'][0]['data'],
                            'backgroundColor' => '#4e73df',
                            'borderColor' => '#4e73df',
                            'borderWidth' => 1
                        ],
                        [
                            'label' => __('Refunds'),
                            'data'  => $visualData['refunds_data']['datasets'][0]['data'],
                            'backgroundColor' => '#e74a3b',
                            'borderColor' => '#e74a3b',
                            'borderWidth' => 1
                        ]
                    ]
                ],
            ]
        );

        $dashboard['widgets']['components'][] = $this->getWidget(
            type: 'chart_display',
            data: [
                'value' => $total['total_invoices'],
                'type' => 'number',
                'description'   => __('Total invoices')
            ],
            visual: [
                'type' => 'line',
                'value' => [
                    'labels'  => $visualData['invoices_data']['labels'],
                    'currency_codes' => $visualData['invoices_data']['currency_codes'],
                    /* 'datasets'    => $visualData['invoices_data']['datasets'] */
                    //TODO: new datasets
                    'datasets'    => [
                        [
                            'label' => __('Fist Data'),
                            'data'  => [420,740,660,50,40,1000],
                            'backgroundColor' => '#4e73df',
                            'borderColor' => '#4e73df',
                            'borderWidth' => 1
                        ],
                        [
                            'label' => __('Second Data'),
                            'data'  => [100,200,550,150,140,1000],
                            'backgroundColor' => '#e74a3b',
                            'borderColor' => '#e74a3b',
                            'borderWidth' => 1
                        ]
                    ]
                ],
            ]
        );

        $dashboard['widgets']['components'][] = $this->getWidget(
            type: 'chart_display',
            data: [
                'value' => $total['total_invoices'],
                'type' => 'number',
                'description'   => __('Total invoices')
            ],
            visual: [
                'type' => 'pie',
                'value' => [
                    'labels' => ['A', 'B', 'C'],
                    'currency_codes' => $visualData['invoices_data']['currency_codes'],
                    /* 'datasets'    => $visualData['invoices_data']['datasets'] */
                    //TODO: new datasets
                    'datasets'    => [
                        [
                            'data' => [540, 325, 702],
                            'backgroundColor' => '#00ffff',
                            'borderColor' => '#ff7f00',
                            'borderWidth' => 1
                        ]
                    ]
                ],
            ]
        );

        return $dashboard;
    }

    public function asController(ActionRequest $request): Response
    {
        $group = group();
        $this->initialisationFromGroup($group, $request)->withTabDashboardInterval();
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
