<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:40:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\WithDashboard;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\UI\Organisation\OrgDashboardIntervalTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowOrganisationDashboard extends OrgAction
{
    use AsAction;
    use WithDashboard;

    public function authorize(ActionRequest $request): bool
    {
        return in_array($this->organisation->id, $request->user()->authorisedOrganisations()->pluck('id')->toArray());
    }

    public function handle(Organisation $organisation, ActionRequest $request): Response
    {
        $userSettings = $request->user()->settings;

        return Inertia::render(
            'Dashboard/OrganisationDashboard',
            [
                'breadcrumbs'     => $this->getBreadcrumbs($request->route()->originalParameters(), __('Dashboard')),
                'dashboard_stats' => $this->getDashboardInterval($organisation, $userSettings),
            ]
        );
    }

    public function getDashboardInterval(Organisation $organisation, array $userSettings): array
    {
        $selectedInterval = Arr::get($userSettings, 'selected_interval', 'all');
        $shops            = $organisation->shops;
        $shopCurrencies   = [];
        foreach ($shops as $shop) {
            $shopCurrencies[] = $shop->currency->symbol;
        }
        $shopCurrenciesSymbol = implode('/', array_unique($shopCurrencies));

        $dashboard = [
            'interval_options' => $this->getIntervalOptions(),
            'settings'         => [
                'db_settings'          => $userSettings,
                'key_currency'         => 'org',
                'key_shop'             => 'open',
                'selected_shop_closed' => Arr::get($userSettings, 'selected_shop_closed', 'closed'),
                'selected_shop_open'   => Arr::get($userSettings, 'selected_shop_open', 'open'),
                'options_shop'         => [
                    [
                        'value' => 'open',
                        'label' => __('Open')
                    ],
                    [
                        'value' => 'closed',
                        'label' => __('Closed')
                    ]
                ],
                'options_currency'     => [
                    [
                        'value' => 'org',
                        'label' => $organisation->currency->symbol,
                    ],
                    [
                        'value' => 'shop',
                        'label' => $shopCurrenciesSymbol,
                    ]
                ]
            ],
            'current' => $this->tabDashboardInterval,
            'table' => [
                [
                    'tab_label' => __('Sales'),
                    'tab_slug'  => 'sales',
                    'tab_icon'  => 'fas fa-chart-line',
                    'type'     => 'table',
                    'data' => null
                ],
            ],
            'widgets'          => [
                'column_count' => 4,
                'components'   => []
            ]
        ];

        $selectedCurrency = Arr::get($userSettings, 'selected_currency_in_org', 'org');
        $total            = [
            'total_sales'    => $organisation->shops->sum(fn ($shop) => $shop->salesIntervals->{"sales_org_currency_$selectedInterval"} ?? 0),
            'total_invoices' => 0,
            'total_refunds'  => 0,
        ];

        $visualData = [];

        $dashboard['table'][0]['data'] = $shops->map(function (Shop $shop) use ($selectedInterval, $organisation, &$dashboard, $selectedCurrency, &$visualData, &$total) {
            $keyCurrency   = $dashboard['settings']['key_currency'];
            $currencyCode  = $selectedCurrency === $keyCurrency ? $organisation->currency->code : $shop->currency->code;
            $salesCurrency = 'sales_'.$selectedCurrency.'_currency';
            if ($selectedCurrency === 'shop') {
                $salesCurrency = 'sales';
            }
            $responseData = [
                'name'          => $shop->name,
                'slug'          => $shop->slug,
                'code'          => $shop->code,
                'type'          => $shop->type,
                'currency_code' => $currencyCode,
                'state'         => $shop->state,
                'route'         => $shop->type == ShopTypeEnum::FULFILMENT
                    ? [
                        'name'       => 'grp.org.fulfilments.show.operations.dashboard',
                        'parameters' => [
                            'organisation' => $organisation->slug,
                            'fulfilment'   => $shop->slug
                        ]
                    ]
                    : [
                        'name'       => 'grp.org.shops.show.dashboard',
                        'parameters' => [
                            'organisation' => $organisation->slug,
                            'shop'         => $shop->slug
                        ]
                    ]
            ];

            if ($shop->salesIntervals !== null) {
                $responseData['interval_percentages']['sales']     = $this->getIntervalPercentage(
                    $shop->salesIntervals,
                    $salesCurrency,
                    $selectedInterval,
                );
                $visualData['sales_data']['labels'][]              = $shop->code;
                $visualData['sales_data']['currency_codes'][]      = $currencyCode;
                $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
            }

            if ($shop->orderingIntervals !== null) {
                $responseData['interval_percentages']['invoices']     = $this->getIntervalPercentage(
                    $shop->orderingIntervals,
                    'invoices',
                    $selectedInterval,
                );
                $responseData['interval_percentages']['refunds']      = $this->getIntervalPercentage(
                    $shop->orderingIntervals,
                    'refunds',
                    $selectedInterval,
                );
                $total['total_invoices']                              += $responseData['interval_percentages']['invoices']['amount'];
                $total['total_refunds']                               += $responseData['interval_percentages']['refunds']['amount'];
                $visualData['invoices_data']['labels'][]              = $shop->code;
                $visualData['invoices_data']['currency_codes'][]      = $currencyCode;
                $visualData['invoices_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['invoices']['amount'];
            }

            return $responseData;
        })->toArray();

        $dashboard['total'] = $total;

        $combinedGraphSales = array_map(null, $visualData['sales_data']['labels'], $visualData['sales_data']['currency_codes'], $visualData['sales_data']['datasets'][0]['data']);

        usort($combinedGraphSales, function ($a, $b) {
            return floatval($b[2]) <=> floatval($a[2]);
        });

        $visualData['sales_data']['labels'] = array_column($combinedGraphSales, 0);
        $visualData['sales_data']['currency_codes'] = array_column($combinedGraphSales, 1);
        $visualData['sales_data']['datasets'][0]['data'] = array_column($combinedGraphSales, 2);

        $dashboard['widgets']['components'][] = $this->getWidget(
            type: 'chart_display',
            data: [
                'status'        => $total['total_sales'] < 0 ? 'danger' : '',
                'value'         => $total['total_sales'],
                'currency_code' => $organisation->currency->code,
                'type'          => 'currency',
                'description'   => __('Total sales')
            ],
            visual: [
                'type'  => 'doughnut',
                'value' => [
                    'labels'         => Arr::get($visualData, 'sales_data.labels'),
                    'currency_codes' => Arr::get($visualData, 'sales_data.currency_codes'),
                    'datasets'       => Arr::get($visualData, 'sales_data.datasets'),
                ],
            ]
        );

        $combinedInvoices = array_map(null, $visualData['invoices_data']['labels'], $visualData['invoices_data']['currency_codes'], $visualData['invoices_data']['datasets'][0]['data']);

        usort($combinedInvoices, function ($a, $b) {
            return floatval($b[2]) <=> floatval($a[2]);
        });

        $visualData['invoices_data']['labels'] = array_column($combinedInvoices, 0);
        $visualData['invoices_data']['currency_codes'] = array_column($combinedInvoices, 1);
        $visualData['invoices_data']['datasets'][0]['data'] = array_column($combinedInvoices, 2);

        $dashboard['widgets']['components'][] = $this->getWidget(
            type: 'chart_display',
            data: [
                'value'       => $total['total_invoices'],
                'type'        => 'number',
                'description' => __('Total invoices')
            ],
            visual: [
                'type'  => 'doughnut',
                'value' => [
                    'labels'         => Arr::get($visualData, 'invoices_data.labels'),
                    'currency_codes' => Arr::get($visualData, 'invoices_data.currency_codes'),
                    'datasets'       => Arr::get($visualData, 'invoices_data.datasets'),

                ],
            ]
        );

        return $dashboard;
    }

    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request)->withTabDashboardInterval(OrgDashboardIntervalTabsEnum::values());

        return $this->handle($organisation, $request);
    }

    public function getBreadcrumbs(array $routeParameters, $label = null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-tachometer-alt-fast',
                    'label' => $label,
                    'route' => [
                        'name'       => 'grp.org.dashboard.show',
                        'parameters' => $routeParameters
                    ]
                ]

            ],

        ];
    }
}
