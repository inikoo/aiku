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
        $selectedShopState = Arr::get($userSettings, 'selected_shop_state', 'open');

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
                'selected_shop_state' => $selectedShopState,
                'options_currency' => [
                    [
                        'value' => 'grp',
                        'label' => $group->currency->symbol,
                    ],
                    [
                        'value' => 'org',
                        'label' => $orgCurrenciesSymbol,
                    ]
                ],
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
            ],
            'currency_code'    => $group->currency->code,
            'current'          => $this->tabDashboardInterval,
            'table'            => [
                [
                    'tab_label' => __('Invoice per organisation'),
                    'tab_slug'  => 'invoice_organisations',
                    'tab_icon'  => 'fas fa-chart-line',
                    'type'      => 'table',
                    'data'      => null
                ],
                [
                    'tab_label' => __('Invoice per store'),
                    'tab_slug'  => 'invoice_shops',
                    'tab_icon'  => 'fal fa-shopping-cart',
                    'type'      => 'table',
                    'data'      => null
                ]
            ],
            'widgets'          => [
                'column_count' => 5,
                'components'   => []
            ]
        ];

        if ($selectedCurrency == 'org') {
            if ($group->currency->symbol != $orgCurrenciesSymbol) {
                data_forget($dashboard, 'currency_code');
            }
        }

        if ($this->tabDashboardInterval == GroupDashboardIntervalTabsEnum::INVOICE_ORGANISATIONS->value) {
            $dashboard['table'][0]['data'] = $this->getInvoiceOrganisation($group, $selectedInterval, $selectedCurrency, $organisations, $dashboard, $total);
        } elseif ($this->tabDashboardInterval == GroupDashboardIntervalTabsEnum::INVOICE_SHOPS->value) {
            $shops                         = $group->shops->whereIn('organisation_id', $organisations->pluck('id')->toArray())->where('state', $selectedShopState);
            $dashboard['table'][1]['data'] = $this->getInvoiceShops($group, $shops, $selectedInterval, $dashboard, $selectedCurrency, $total);
        }

        $dashboard['total'] = $total;

        return $dashboard;
    }

    public function getInvoiceOrganisation(Group $group, $selectedInterval, $selectedCurrency, $organisations, &$dashboard, &$total): array
    {

        $data = [];
        $visualData = [];

        $this->setDashboardTableData(
            $group,
            $organisations,
            $dashboard,
            $visualData,
            $data,
            $selectedCurrency,
            $selectedInterval,
            fn ($child) => [
                'route' => [
                    'name'       => 'grp.org.dashboard.show',
                    'parameters' => [
                    'organisation' => $child->slug,
                    ]
                ],
                'route_invoice' => [
                    'name'       => 'grp.org.accounting.invoices.index',
                    'parameters' => [
                    'organisation'  => $child->slug,
                    'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ],
                'route_refund'  => [
                    'name'       => 'grp.org.accounting.refunds.index',
                    'parameters' => [
                    'organisation'  => $child->slug,
                    'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                    ]
                ]
            ]
        );


        $total = $dashboard['total'];

        if (!Arr::get($visualData, 'sales_data')) {
            return $data;
        }

        if (array_filter(Arr::get($visualData, 'sales_data.datasets.0.data'), fn ($value) => $value !== '0.00')) {
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

    public function getInvoiceShops(Group $group, $shops, $selectedInterval, &$dashboard, $selectedCurrency, &$total): array
    {
        $visualData = [];
        $data = [];

        $this->setDashboardTableData(
            $group,
            $shops,
            $dashboard,
            $visualData,
            $data,
            $selectedCurrency,
            $selectedInterval,
            function ($child) use ($selectedInterval) {
                $routes = [
                    'route'         => [
                        'name'       => 'grp.org.shops.show.dashboard',
                        'parameters' => [
                            'organisation' => $child->organisation->slug,
                            'shop'         => $child->slug
                        ]
                    ],
                    'route_invoice' => [
                        'name'       => 'grp.org.shops.show.ordering.invoices.index',
                        'parameters' => [
                            'organisation'  => $child->organisation->slug,
                            'shop'          => $child->slug,
                            'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                        ]
                    ],
                    'route_refund'  => [
                        'name'       => 'grp.org.shops.show.ordering.refunds.index',
                        'parameters' => [
                            'organisation'  => $child->organisation->slug,
                            'shop'          => $child->slug,
                            'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                        ]
                    ],
                ];

                if ($child->type == ShopTypeEnum::FULFILMENT) {
                    $routes['route']         = [
                        'name'       => 'grp.org.fulfilments.show.operations.dashboard',
                        'parameters' => [
                            'organisation' => $child->organisation->slug,
                            'fulfilment'   => $child->slug
                        ]
                    ];
                    $routes['route_invoice'] = [
                        'name'       => 'grp.org.fulfilments.show.operations.invoices.all.index',
                        'parameters' => [
                            'organisation'  => $child->organisation->slug,
                            'fulfilment'    => $child->slug,
                            'between[date]' => $this->getDateIntervalFilter($selectedInterval)
                        ]
                    ];
                    $routes['route_refund']  = [
                        'name'       => 'grp.org.fulfilments.show.operations.invoices.refunds.index',
                        'parameters' => [
                            'organisation'  => $child->organisation->slug,
                            'fulfilment'    => $child->slug,
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

        if (array_filter(Arr::get($visualData, 'sales_data.datasets.0.data'), fn ($value) => $value !== '0.00')) {
            $combined = $this->sortVisualDataset($visualData['sales_data']['labels'], $visualData['sales_data']['currency_codes'], $visualData['sales_data']['datasets'][0]['data']);

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
        }

        if (array_filter(Arr::get($visualData, 'invoices_data.datasets.0.data'))) {
            $combinedInvoices = $this->sortVisualDataset($visualData['invoices_data']['labels'], $visualData['invoices_data']['currency_codes'], $visualData['invoices_data']['datasets'][0]['data']);

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
