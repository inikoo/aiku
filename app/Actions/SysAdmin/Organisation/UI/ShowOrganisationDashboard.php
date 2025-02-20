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
use Illuminate\Support\Number;
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
            'current' => $this->tabDashboardInterval,
            'table' => [
                [
                    'tab_label' => __('Invoice per store'),
                    'tab_slug'  => 'invoices',
                    'tab_icon'  => 'fal fa-file-invoice-dollar',
                    'type'     => 'table',
                    'data' => null
                ],
                [
                    'tab_label' => __('Invoices categories'),
                    'tab_slug'  => 'invoice_categories',
                    'tab_icon'  => 'fal fa-sitemap',
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

        // $total = [
        //     'total_sales'                => 0,
        //     'total_sales_percentages'    => 0,
        //     'total_sales_ly'             => 0,
        //     'total_invoices'             => 0,
        //     'total_invoices_percentages' => 0,
        //     'total_refunds'              => 0,
        //     'total_refunds_percentages'  => 0,
        // ];

        $total = [
            'total_sales'                => 0,
            'total_sales_percentages'    => 0,

            'total_invoices'             => 0,
            'total_invoices_percentages' => 0,

            'total_refunds'              => 0,
            'total_refunds_percentages'  => 0,
        ];


        if ($this->tabDashboardInterval == OrgDashboardIntervalTabsEnum::INVOICES->value) {

            $total['total_sales']          = $shops->sum(fn ($shop) => $shop->salesIntervals->{"sales_grp_currency_$selectedInterval"} ?? 0);
            $total['total_sales_percentages'] = $this->calculatePercentageIncrease($total['total_sales'], $shops->sum(fn ($shop) => $shop->salesIntervals->{"sales_grp_currency_$selectedInterval" . '_ly'} ?? 0));

            $total['total_invoices']       = $shops->sum(fn ($shop) => $shop->orderingIntervals->{"invoices_$selectedInterval"} ?? 0);
            $total['total_invoices_percentages'] = $this->calculatePercentageIncrease($total['total_invoices'], $shops->sum(fn ($shop) => $shop->orderingIntervals->{"invoices_$selectedInterval" . '_ly'} ?? 0));

            $total['total_refunds']        = $shops->sum(fn ($shop) => $shop->orderingIntervals->{"refunds_$selectedInterval"} ?? 0);
            $total['total_refunds_percentages'] = $this->calculatePercentageIncrease($total['total_refunds'], $shops->sum(fn ($shop) => $shop->orderingIntervals->{"refunds_$selectedInterval" . '_ly'} ?? 0));


            $dashboard['total_tooltip'] = [
                'total_sales' => __("Last year sales") . ": " . Number::currency($shops->sum(fn ($shop) => $shop->salesIntervals->{"sales_grp_currency_$selectedInterval" . '_ly'} ?? 0), $organisation->currency->code),
                'total_invoices' => __("Last year invoices") . ": " . Number::currency($shops->sum(fn ($shop) => $shop->orderingIntervals->{"invoices_$selectedInterval" . '_ly'} ?? 0), $organisation->currency->code),
                'total_refunds' => __("Last year refunds") . ": " . Number::currency($shops->sum(fn ($shop) => $shop->orderingIntervals->{"refunds_$selectedInterval" . '_ly'} ?? 0), $organisation->currency->code),
            ];


            // $total['total_sales']          = $shops->sum(fn ($shop) => $shop->salesIntervals->{"sales_org_currency_$selectedInterval"} ?? 0);
            $dashboard['table'][0]['data'] = $this->getInvoices($organisation, $shops, $selectedInterval, $dashboard, $selectedCurrency, $total);

        } elseif ($this->tabDashboardInterval == OrgDashboardIntervalTabsEnum::INVOICE_CATEGORIES->value) {
            $invoiceCategories = $organisation->invoiceCategories;
            $total['total_sales']  = $invoiceCategories->sum(fn ($invoiceCategory) => $invoiceCategory->salesIntervals->{"sales_org_currency_$selectedInterval"} ?? 0);
            $dashboard['table'][1]['data'] = $this->getInvoiceCategories($organisation, $invoiceCategories, $selectedInterval, $dashboard, $selectedCurrency, $total);
        }

        $dashboard['total'] = $total;


        return $dashboard;
    }

    public function getInvoices(Organisation $organisation, $shops, $selectedInterval, &$dashboard, $selectedCurrency, &$total): array
    {
        $visualData = [];

        // $data = $shops->map(function (Shop $shop) use ($selectedInterval, $organisation, &$dashboard, $selectedCurrency, &$visualData, &$total) {
        //     $keyCurrency   = $dashboard['settings']['key_currency'];
        //     $currencyCode  = $selectedCurrency === $keyCurrency ? $organisation->currency->code : $shop->currency->code;
        //     $salesCurrency = 'sales_'.$selectedCurrency.'_currency';
        //     if ($selectedCurrency === 'shop') {
        //         $salesCurrency = 'sales';
        //     }
        //     $responseData = [
        //         'name'          => $shop->name,
        //         'slug'          => $shop->slug,
        //         'code'          => $shop->code,
        //         'type'          => $shop->type,
        //         'currency_code' => $currencyCode,
        //         'state'         => $shop->state,
        //         'route'         => [
        //             'name'       => 'grp.org.shops.show.dashboard',
        //             'parameters' => [
        //                 'organisation' => $organisation->slug,
        //                 'shop'         => $shop->slug
        //             ]
        //         ],
        //         'route_invoice' => [
        //             'name'       => 'grp.org.shops.show.ordering.invoices.index',
        //             'parameters' => [
        //                 'organisation' => $organisation->slug,
        //                 'shop' => $shop->slug,
        //                 'between[date]' => $this->getDateIntervalFilter($selectedInterval)
        //             ]
        //         ],
        //         'route_refund' => [
        //             'name'       => 'grp.org.shops.show.ordering.refunds.index',
        //             'parameters' => [
        //                 'organisation' => $organisation->slug,
        //                 'shop'        => $shop->slug,
        //                 'between[date]' => $this->getDateIntervalFilter($selectedInterval)
        //             ]
        //         ],
        //     ];

        //     if ($shop->type == ShopTypeEnum::FULFILMENT) {
        //         $responseData['route'] = [
        //             'name'       => 'grp.org.fulfilments.show.operations.dashboard',
        //             'parameters' => [
        //                 'organisation' => $organisation->slug,
        //                 'fulfilment'   => $shop->slug
        //             ]
        //         ];
        //         $responseData['route_invoice'] = [
        //             'name'       => 'grp.org.fulfilments.show.operations.invoices.all.index',
        //             'parameters' => [
        //                 'organisation' => $organisation->slug,
        //                 'fulfilment'   => $shop->slug,
        //                 'between[date]' => $this->getDateIntervalFilter($selectedInterval)
        //             ]
        //         ];
        //         $responseData['route_refund'] = [
        //             'name'       => 'grp.org.fulfilments.show.operations.invoices.refunds.index',
        //             'parameters' => [
        //                 'organisation' => $organisation->slug,
        //                 'fulfilment'   => $shop->slug,
        //                 'between[date]' => $this->getDateIntervalFilter($selectedInterval)
        //             ]
        //         ];
        //     }

        //     if ($shop->salesIntervals !== null) {
        //         // data sales
        //         $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
        //             $shop->salesIntervals,
        //             $salesCurrency,
        //             $selectedInterval,
        //             __("Last year sales") . ": ",
        //             $currencyCode
        //         );

        //         // dd($responseData['interval_percentages']['sales']);

        //         $total['total_sales_ly']                  += $responseData['interval_percentages']['sales']['amount_ly'] ?? 0;

        //         // visual sales
        //         $visualData['sales_data']['labels'][]              = $shop->code;
        //         $visualData['sales_data']['currency_codes'][]      = $currencyCode;
        //         $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
        //         $total['total_sales_percentages']                  += $responseData['interval_percentages']['sales']['percentage'] ?? 0;
        //     }

        //     if ($shop->orderingIntervals !== null) {
        //         // data invoices
        //         $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
        //             $shop->orderingIntervals,
        //             'invoices',
        //             $selectedInterval,
        //         );
        //         // data refunds
        //         $responseData['interval_percentages']['refunds'] = $this->getIntervalPercentage(
        //             $shop->orderingIntervals,
        //             'refunds',
        //             $selectedInterval,
        //         );

        //         $total['total_invoices_percentages'] += $responseData['interval_percentages']['invoices']['percentage'] ?? 0;
        //         $total['total_invoices']             += $responseData['interval_percentages']['invoices']['amount'];

        //         $total['total_refunds'] += $responseData['interval_percentages']['refunds']['amount'];
        //         $total['total_refunds_percentages']              += $responseData['interval_percentages']['refunds']['percentage'] ?? 0;


        //         // visual data
        //         $visualData['invoices_data']['labels'][]              = $shop->code;
        //         $visualData['invoices_data']['currency_codes'][]      = $currencyCode;
        //         $visualData['invoices_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['invoices']['amount'];

        //         $visualData['refunds_data']['labels'][]              = $shop->code;
        //         $visualData['refunds_data']['currency_codes'][]      = $currencyCode;
        //         $visualData['refunds_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['refunds']['amount'];
        //     }


        //     return $responseData;
        // })->toArray();

        // $total['total_tooltip_ly'] = __("Last year sales") . ": " . Number::currency($total['total_sales_ly'], $organisation->currency->code);

        // $dashboard['total'] = $total;

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

        return $data;
    }

    public function getInvoiceCategories(Organisation $organisation, $invoiceCategories, $selectedInterval, &$dashboard, $selectedCurrency, &$total): array
    {
        $visualData = [];

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
