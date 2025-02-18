<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Dec 2024 00:41:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dashboards;

use App\Actions\OrgAction;
use App\Actions\Traits\WithDashboard;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Enums\UI\Group\GroupDashboardIntervalTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowGroupDashboard extends OrgAction
{
    use WithDashboard;

    public function handle(Group $group, User $user): Response
    {
        $userSettings = $user->settings;

        return Inertia::render(
            'Dashboard/GrpDashboard',
            [
                'breadcrumbs'     => $this->getBreadcrumbs(__('Dashboard')),
                'dashboard_stats' => $this->getDashboardInterval($group, $userSettings),
            ]
        );
    }

    public function getDashboardInterval(Group $group, array $userSettings): array
    {
        $selectedInterval = Arr::get($userSettings, 'selected_interval', 'all');
        $selectedAmount   = Arr::get($userSettings, 'selected_amount', true);
        $selectedCurrency = Arr::get($userSettings, 'selected_currency_in_grp', 'grp');

        $organisations = $group->organisations()->where('type', '!=', OrganisationTypeEnum::AGENT->value)->get();
        $orgCurrencies = [];
        /** @var Organisation $organisation */
        foreach ($organisations as $organisation) {
            $orgCurrencies[] = $organisation->currency->symbol;
        }
        $orgCurrenciesSymbol = implode('/', array_unique($orgCurrencies));

        $dashboard = [
            'interval_options' => $this->getIntervalOptions(),
            'settings'         => [
                'selected_amount'  => $selectedAmount,
                'db_settings'      => $userSettings,
                'key_currency'     => 'grp',
                'options_currency' => [
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
            'current'          => $this->tabDashboardInterval,
            'table'            => [
                [
                    'tab_label' => __('Sales'),
                    'tab_slug'  => 'sales',
                    'tab_icon'  => 'fas fa-chart-line',
                    'type'      => 'table',
                    'data'      => null
                ],
                [
                    'tab_label' => __('Shops'),
                    'tab_slug'  => 'shops',
                    'tab_icon'  => 'fal fa-shopping-cart',
                    'type'      => 'table',
                    'data'      => null
                ]
            ],
            'widgets'          => [
                'column_count' => 4,
                'components'   => []
            ]
        ];


        $total = [
            'total_sales'                => 0,
            'total_sales_percentages'    => 0,
            'total_invoices'             => 0,
            'total_invoices_percentages' => 0,
            'total_refunds'              => 0,
        ];

        if ($this->tabDashboardInterval == GroupDashboardIntervalTabsEnum::SALES->value) {
            $total['total_sales']          = $organisations->sum(fn ($organisation) => $organisation->salesIntervals->{"sales_grp_currency_$selectedInterval"} ?? 0);
            $dashboard['table'][0]['data'] = $this->getSales($group, $selectedInterval, $selectedCurrency, $organisations, $dashboard, $total);
        } elseif ($this->tabDashboardInterval == GroupDashboardIntervalTabsEnum::SHOPS->value) {
            $shops                         = $group->shops->whereIn('organisation_id', $organisations->pluck('id')->toArray());
            $total['total_sales']          = $shops->sum(fn ($shop) => $shop->salesIntervals->{"sales_grp_currency_$selectedInterval"} ?? 0);
            $dashboard['table'][1]['data'] = $this->getShops($group, $shops, $selectedInterval, $dashboard, $selectedCurrency, $total);
        }

        return $dashboard;
    }

    public function getSales(Group $group, $selectedInterval, $selectedCurrency, $organisations, &$dashboard, &$total): array
    {
        $visualData = [];

        $data = $organisations->map(function (Organisation $organisation) use ($selectedInterval, $group, &$dashboard, $selectedCurrency, &$visualData, &$total) {
            $keyCurrency   = $dashboard['settings']['key_currency'];
            $currencyCode  = $selectedCurrency === $keyCurrency ? $group->currency->code : $organisation->currency->code;
            $salesCurrency = 'sales_'.$selectedCurrency.'_currency';
            $responseData  = [
                'name'          => $organisation->name,
                'slug'          => $organisation->slug,
                'code'          => $organisation->code,
                'type'          => $organisation->type,
                'currency_code' => $currencyCode,
                'route'         => [
                    'name'       => 'grp.org.dashboard.show',
                    'parameters' => [
                        'organisation' => $organisation->slug,
                    ]
                ],
                'route_invoice' => [
                    'name'       => 'grp.org.accounting.invoices.index',
                    'parameters' => [
                        'organisation'  => $organisation->slug,
                        'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ],
                'route_refund'  => [
                    'name'       => 'grp.org.accounting.refunds.index',
                    'parameters' => [
                        'organisation'  => $organisation->slug,
                        'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ],

            ];


            if ($organisation->salesIntervals !== null) {
                $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                    $organisation->salesIntervals,
                    $salesCurrency,
                    $selectedInterval,
                );
                // visual sales
                $visualData['sales_data']['labels'][]              = $organisation->code;
                $visualData['sales_data']['currency_codes'][]      = $currencyCode;
                $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
                $total['total_sales_percentages']                  += $responseData['interval_percentages']['sales']['percentage'] ?? 0;
            }

            if ($organisation->orderingIntervals !== null) {
                $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
                    $organisation->orderingIntervals,
                    'invoices',
                    $selectedInterval,
                );
                $responseData['interval_percentages']['refunds']  = $this->getIntervalPercentage(
                    $organisation->orderingIntervals,
                    'refunds',
                    $selectedInterval,
                );
                $total['total_invoices_percentages']              += $responseData['interval_percentages']['invoices']['percentage'] ?? 0;

                $total['total_invoices'] += $responseData['interval_percentages']['invoices']['amount'];
                $total['total_refunds']  += $responseData['interval_percentages']['refunds']['amount'];

                $visualData['invoices_data']['labels'][]              = $organisation->code;
                $visualData['invoices_data']['currency_codes'][]      = $currencyCode;
                $visualData['invoices_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['invoices']['amount'];

                $visualData['refunds_data']['labels'][]              = $organisation->code;
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
                'currency_code' => $group->currency->code,
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

    public function getShops(Group $group, $shops, $selectedInterval, &$dashboard, $selectedCurrency, &$total): array
    {
        $visualData = [];

        $data = $shops->map(function (Shop $shop) use ($selectedInterval, $group, &$dashboard, $selectedCurrency, &$visualData, &$total) {
            $keyCurrency   = $dashboard['settings']['key_currency'];
            $currencyCode  = $selectedCurrency === $keyCurrency ? $group->currency->code : $shop->organisation->currency->code;
            $salesCurrency = 'sales_'.$selectedCurrency.'_currency';
            $responseData  = [
                'name'          => $shop->name,
                'slug'          => $shop->slug,
                'code'          => $shop->code,
                'type'          => $shop->type,
                'currency_code' => $currencyCode,
                'route'         => [
                    'name'       => 'grp.org.shops.show.dashboard',
                    'parameters' => [
                        'organisation' => $shop->organisation->slug,
                        'shop'         => $shop->slug
                    ]
                ],
                'route_invoice' => [
                    'name'       => 'grp.org.shops.show.ordering.invoices.index',
                    'parameters' => [
                        'organisation'  => $shop->organisation->slug,
                        'shop'          => $shop->slug,
                        'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ],
                'route_refund'  => [
                    'name'       => 'grp.org.shops.show.ordering.refunds.index',
                    'parameters' => [
                        'organisation'  => $shop->organisation->slug,
                        'shop'          => $shop->slug,
                        'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ],
            ];

            if ($shop->type == ShopTypeEnum::FULFILMENT) {
                $responseData['route']         = [
                    'name'       => 'grp.org.fulfilments.show.operations.dashboard',
                    'parameters' => [
                        'organisation' => $shop->organisation->slug,
                        'fulfilment'   => $shop->slug
                    ]
                ];
                $responseData['route_invoice'] = [
                    'name'       => 'grp.org.fulfilments.show.operations.invoices.all.index',
                    'parameters' => [
                        'organisation'  => $shop->organisation->slug,
                        'fulfilment'    => $shop->slug,
                        'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ];
                $responseData['route_refund']  = [
                    'name'       => 'grp.org.fulfilments.show.operations.invoices.refunds.index',
                    'parameters' => [
                        'organisation'  => $shop->organisation->slug,
                        'fulfilment'    => $shop->slug,
                        'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ];
            }

            if ($shop->salesIntervals !== null) {
                // data sales
                $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                    $shop->salesIntervals,
                    $salesCurrency,
                    $selectedInterval,
                );

                // visual sales
                $visualData['sales_data']['labels'][]              = $shop->code;
                $visualData['sales_data']['currency_codes'][]      = $currencyCode;
                $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
                $total['total_sales_percentages']                  += $responseData['interval_percentages']['sales']['percentage'] ?? 0;
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

                $total['total_invoices_percentages'] += $responseData['interval_percentages']['invoices']['percentage'] ?? 0;
                $total['total_invoices']             += $responseData['interval_percentages']['invoices']['amount'];

                $total['total_refunds'] += $responseData['interval_percentages']['refunds']['amount'];

                // visual data
                $visualData['invoices_data']['labels'][]              = $shop->code;
                $visualData['invoices_data']['currency_codes'][]      = $currencyCode;
                $visualData['invoices_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['invoices']['amount'];

                $visualData['refunds_data']['labels'][]              = $shop->code;
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
                'currency_code' => $group->currency->code,
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

    public function asController(ActionRequest $request): Response
    {
        $group = group();
        $this->initialisationFromGroup($group, $request)->withTabDashboardInterval(GroupDashboardIntervalTabsEnum::values());

        return $this->handle($group, $request->user());
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
