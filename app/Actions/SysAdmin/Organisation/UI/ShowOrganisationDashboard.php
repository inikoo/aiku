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
use App\Models\Accounting\InvoiceCategory;
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
        $selectedAmount   = Arr::get($userSettings, 'selected_amount', true);
        $selectedShopState = Arr::get($userSettings, 'selected_shop_state', 'open');
        $shops            = $organisation->shops->where('state', $selectedShopState);
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
                'selected_amount'      => $selectedAmount,
                'selected_shop_state'  => $selectedShopState,
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
            'currency_code' => $organisation->currency->code,
            'current' => $this->tabDashboardInterval,
            'table' => [
                [
                    'tab_label' => __('Invoice per store'),
                    'tab_slug'  => 'invoices',
                    'tab_icon'  => 'fal fa-file-invoice-dollar',
                    'type'     => 'table',
                    'data' => null
                ],
                (!app()->isProduction()) ?
                [
                    'tab_label' => __('Invoices categories'),
                    'tab_slug'  => 'invoice_categories',
                    'tab_icon'  => 'fal fa-sitemap',
                    'type'     => 'table',
                    'data' => null
                ] : []
            ],
            'widgets'          => [
                'column_count' => 5,
                'components'   => []
            ]
        ];

        $selectedCurrency = Arr::get($userSettings, 'selected_currency_in_org', 'org');

        if ($selectedCurrency == 'shop') {
            data_forget($dashboard, 'currency_code');
        }

        if ($this->tabDashboardInterval == OrgDashboardIntervalTabsEnum::INVOICES->value) {
            $dashboard['table'][0]['data'] = $this->getInvoices($organisation, $shops, $selectedInterval, $dashboard, $selectedCurrency, $total);
        } elseif ($this->tabDashboardInterval == OrgDashboardIntervalTabsEnum::INVOICE_CATEGORIES->value) {
            if (!app()->isProduction()) {
                $invoiceCategories = $organisation->invoiceCategories;
                $total['total_sales']  = $invoiceCategories->sum(fn ($invoiceCategory) => $invoiceCategory->salesIntervals->{"sales_org_currency_$selectedInterval"} ?? 0);
                $dashboard['table'][1]['data'] = $this->getInvoiceCategories($organisation, $invoiceCategories, $selectedInterval, $dashboard, $selectedCurrency, $total);
            }
        }


        return $dashboard;
    }

    public function getInvoices(Organisation $organisation, $shops, $selectedInterval, &$dashboard, $selectedCurrency, &$total): array
    {
        $visualData = [];

        $data = [];

        $this->setDashboardTableData(
            $organisation,
            $shops,
            $dashboard,
            $visualData,
            $data,
            $selectedCurrency,
            $selectedInterval,
            function ($child) use ($selectedInterval, $organisation) {
                $routes = [
                            'route'         => [
                    'name'       => 'grp.org.shops.show.dashboard',
                    'parameters' => [
                        'organisation' => $organisation->slug,
                        'shop'         => $child->slug
                    ]
                ],
                'route_invoice' => [
                    'name'       => 'grp.org.shops.show.ordering.invoices.index',
                    'parameters' => [
                        'organisation' => $organisation->slug,
                        'shop' => $child->slug,
                        'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ],
                'route_refund' => [
                    'name'       => 'grp.org.shops.show.ordering.refunds.index',
                    'parameters' => [
                        'organisation' => $organisation->slug,
                        'shop'        => $child->slug,
                        'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ],
                ];

                if ($child->type == ShopTypeEnum::FULFILMENT) {
                    $routes['route'] = [
                        'name'       => 'grp.org.fulfilments.show.operations.dashboard',
                        'parameters' => [
                            'organisation' => $organisation->slug,
                            'fulfilment'   => $child->slug
                        ]
                    ];
                    $routes['route_invoice'] = [
                        'name'       => 'grp.org.fulfilments.show.operations.invoices.all.index',
                        'parameters' => [
                            'organisation' => $organisation->slug,
                            'fulfilment'   => $child->slug,
                            'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                        ]
                    ];
                    $routes['route_refund'] = [
                        'name'       => 'grp.org.fulfilments.show.operations.invoices.refunds.index',
                        'parameters' => [
                            'organisation' => $organisation->slug,
                            'fulfilment'   => $child->slug,
                            'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                        ]
                    ];
                }
                return $routes;
            }
        );

        $total = $dashboard['total'];

        if (!Arr::get($visualData, 'sales_data')) {
            return $data;
        }

        if (Arr::get($visualData, 'sales_data.datasets.0.data')) {
            $combined = array_map(null, $visualData['sales_data']['labels'], $visualData['sales_data']['currency_codes'], $visualData['sales_data']['datasets'][0]['data']);

            usort($combined, function ($a, $b) {
                return floatval($b[2]) <=> floatval($a[2]);
            });

            $visualData['sales_data']['labels']              = array_column($combined, 0);
            $visualData['sales_data']['currency_codes']      = array_column($combined, 1);
            $visualData['sales_data']['datasets'][0]['data'] = array_column($combined, 2);

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
                        'labels'         => $visualData['sales_data']['labels'],
                        'currency_codes' => $visualData['sales_data']['currency_codes'],
                        'datasets'       => $visualData['sales_data']['datasets']
                    ],
                ]
            );
        }


        if (array_filter(Arr::get($visualData, 'invoices_data.datasets.0.data'))) {
            $combinedInvoices = array_map(null, $visualData['invoices_data']['labels'], $visualData['invoices_data']['currency_codes'], $visualData['invoices_data']['datasets'][0]['data']);

            usort($combinedInvoices, function ($a, $b) {
                return floatval($b[2]) <=> floatval($a[2]);
            });

            $visualData['invoices_data']['labels']              = array_column($combinedInvoices, 0);
            $visualData['invoices_data']['currency_codes']      = array_column($combinedInvoices, 1);
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
                        'datasets'       => Arr::get($visualData, 'invoices_data.datasets'),
                    ],
                ]
            );

            $averageInvoices = [];
            $totalAvg = 0;
            for ($i = 0; $i < count($combined); $i++) {
                $amount = 0;
                if ($combinedInvoices[$i][2] != 0) {
                    $amount = $combined[$i][2] / $combinedInvoices[$i][2];
                }
                $averageInvoices[$i] = [
                    'name' => $combined[$i][0],
                    'currency_code' => $combined[$i][1],
                    'amount' => $amount,
                ];
                $totalAvg += $amount;
            }

            if ($totalAvg == 0) {
                return $data;
            }


            $dashboard['widgets']['components'][] = $this->getWidget(
                type: 'chart_display',
                data: [
                    'description' => __('Average amount value')
                ],
                visual: [
                    'type'  => 'bar',
                    'value' => [
                        'labels'         => Arr::pluck($averageInvoices, 'name'),
                        'currency_codes' => Arr::pluck($averageInvoices, 'currency_code'),
                        'datasets'       => [
                            [
                                'data' => Arr::pluck($averageInvoices, 'amount')
                            ]
                        ]
                    ],
                ]
            );
        }




        return $data;
    }

    public function getInvoiceCategories(Organisation $organisation, $invoiceCategories, $selectedInterval, &$dashboard, $selectedCurrency, &$total): array
    {
        $visualData = [];
        $data = [];

        $data = $invoiceCategories->map(function (InvoiceCategory $invoiceCategory) use ($selectedInterval, $organisation, &$dashboard, $selectedCurrency, &$visualData, &$total) {
            $keyCurrency   = $dashboard['settings']['key_currency'];
            $currencyCode  = $selectedCurrency === $keyCurrency ? $organisation->currency->code : $invoiceCategory->currency->code;
            $salesCurrency = 'sales_'.$selectedCurrency.'_currency';
            $responseData  = [
                'name'          => $invoiceCategory->name,
                'slug'          => $invoiceCategory->slug,
                'type'          => $invoiceCategory->type,
                'currency_code' => $currencyCode,
                // 'route'         => [
                //     'name'       => 'grp.org.shops.show.dashboard',
                //     'parameters' => [
                //         'organisation' => $shop->organisation->slug,
                //         'shop'         => $shop->slug
                //     ]
                // ],
                // 'route_invoice' => [
                //     'name'       => 'grp.org.shops.show.ordering.invoices.index',
                //     'parameters' => [
                //         'organisation'  => $shop->organisation->slug,
                //         'shop'          => $shop->slug,
                //         'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                //     ]
                // ],
                // 'route_refund'  => [
                //     'name'       => 'grp.org.shops.show.ordering.refunds.index',
                //     'parameters' => [
                //         'organisation'  => $shop->organisation->slug,
                //         'shop'          => $shop->slug,
                //         'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                //     ]
                // ],
            ];

            // if ($shop->type == ShopTypeEnum::FULFILMENT) {
            //     $responseData['route']         = [
            //         'name'       => 'grp.org.fulfilments.show.operations.dashboard',
            //         'parameters' => [
            //             'organisation' => $shop->organisation->slug,
            //             'fulfilment'   => $shop->slug
            //         ]
            //     ];
            //     $responseData['route_invoice'] = [
            //         'name'       => 'grp.org.fulfilments.show.operations.invoices.all.index',
            //         'parameters' => [
            //             'organisation'  => $shop->organisation->slug,
            //             'fulfilment'    => $shop->slug,
            //             'between[date]' => $this->getDateIntervalFilter($selectedInterval)
            //         ]
            //     ];
            //     $responseData['route_refund']  = [
            //         'name'       => 'grp.org.fulfilments.show.operations.invoices.refunds.index',
            //         'parameters' => [
            //             'organisation'  => $shop->organisation->slug,
            //             'fulfilment'    => $shop->slug,
            //             'between[date]' => $this->getDateIntervalFilter($selectedInterval)
            //         ]
            //     ];
            // }

            if ($invoiceCategory->salesIntervals !== null) {
                // data sales
                $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                    $invoiceCategory->salesIntervals,
                    $salesCurrency,
                    $selectedInterval,
                );

                // visual sales
                $visualData['sales_data']['labels'][]              = $invoiceCategory->name;
                $visualData['sales_data']['currency_codes'][]      = $currencyCode;
                $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
                $total['total_sales_percentages']                  += $responseData['interval_percentages']['sales']['percentage'] ?? 0;
            }

            if ($invoiceCategory->orderingIntervals !== null) {
                // data invoices
                $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
                    $invoiceCategory->orderingIntervals,
                    'invoices',
                    $selectedInterval,
                );
                // data refunds
                $responseData['interval_percentages']['refunds'] = $this->getIntervalPercentage(
                    $invoiceCategory->orderingIntervals,
                    'refunds',
                    $selectedInterval,
                );

                $total['total_invoices_percentages'] += $responseData['interval_percentages']['invoices']['percentage'] ?? 0;
                $total['total_invoices']             += $responseData['interval_percentages']['invoices']['amount'];

                $total['total_refunds'] += $responseData['interval_percentages']['refunds']['amount'];
                $total['total_refunds_percentages']              += $responseData['interval_percentages']['refunds']['percentage'] ?? 0;


                // visual data
                $visualData['invoices_data']['labels'][]              = $invoiceCategory->name;
                $visualData['invoices_data']['currency_codes'][]      = $currencyCode;
                $visualData['invoices_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['invoices']['amount'];

                $visualData['refunds_data']['labels'][]              = $invoiceCategory->name;
                $visualData['refunds_data']['currency_codes'][]      = $currencyCode;
                $visualData['refunds_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['refunds']['amount'];
            }

            return $responseData;
        })->toArray();

        $dashboard['total'] = $total;

        if (!Arr::get($visualData, 'sales_data')) {
            return $data;
        }



        $combined = array_map(null, $visualData['sales_data']['labels'], $visualData['sales_data']['currency_codes'], $visualData['sales_data']['datasets'][0]['data']);

        usort($combined, function ($a, $b) {
            return floatval($b[2]) <=> floatval($a[2]);
        });

        $visualData['sales_data']['labels']              = array_column($combined, 0);
        $visualData['sales_data']['currency_codes']      = array_column($combined, 1);
        $visualData['sales_data']['datasets'][0]['data'] = array_column($combined, 2);

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
                    'labels'         => $visualData['sales_data']['labels'],
                    'currency_codes' => $visualData['sales_data']['currency_codes'],
                    'datasets'       => $visualData['sales_data']['datasets']
                ],
            ]
        );

        $combinedInvoices = array_map(null, $visualData['invoices_data']['labels'], $visualData['invoices_data']['currency_codes'], $visualData['invoices_data']['datasets'][0]['data']);

        usort($combinedInvoices, function ($a, $b) {
            return floatval($b[2]) <=> floatval($a[2]);
        });

        $visualData['invoices_data']['labels']              = array_column($combinedInvoices, 0);
        $visualData['invoices_data']['currency_codes']      = array_column($combinedInvoices, 1);
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


        return $data;
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
