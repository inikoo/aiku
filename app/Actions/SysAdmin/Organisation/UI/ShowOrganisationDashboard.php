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
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowOrganisationDashboard extends OrgAction
{
    use AsAction;
    use WithDashboard;

    public function handle(Organisation $organisation, ActionRequest $request): Response
    {
        // $sales   = [
        //         'sales'    => JsonResource::make($organisation->orderingStats),
        //         'currency' => $organisation->currency,
        //         'total'    => collect([
        //             'ytd' => 'sales_ytd',
        //             'qtd' => 'sales_qtd',
        //             'mtd' => 'sales_mtd',
        //             'wtd' => 'sales_wtd',
        //             'lm'  => 'sales_lm',
        //             'lw'  => 'sales_lw',
        //             'ld'  => 'sales_ld',
        //             'tdy' => 'sales_tdy',
        //             '1y'  => 'sales_1y',
        //             '1q'  => 'sales_1q',
        //             '1m'  => 'sales_1m',
        //             '1w'  => 'sales_1w',
        //         ])->mapWithKeys(function ($salesInterval, $key) use ($organisation) {
        //             return [
        //                 $key => [
        //                     'total_sales'    => $organisation->shops->sum(fn ($shop) => $shop->salesIntervals->$salesInterval ?? 0),
        //                     'total_invoices' => $organisation->shops->sum(fn ($shop) => $shop->orderingIntervals->{"invoices_{$key}"} ?? 0),
        //                     'total_refunds'  => $organisation->shops->sum(fn ($shop) => $shop->orderingIntervals->{"refunds_{$key}"} ?? 0),
        //                 ]
        //             ];
        //         })->toArray() + [
        //             'all' => [
        //                 'total_sales'    => $organisation->salesIntervals->sales_org_currency_all ?? 0,
        //                 'total_invoices' => $organisation->orderingIntervals->invoices_all ?? 0,
        //                 'total_refunds'  => $organisation->orderingIntervals->refunds_all ?? 0,
        //             ],
        //         ],
        //         'shops' => $organisation->shops->map(function (Shop $shop) use ($organisation) {
        //             $responseData = [
        //                 'name'      => $shop->name,
        //                 'slug'      => $shop->slug,
        //                 'type'      => $shop->type,
        //                 'currency'  => $shop->currency,
        //                 'state'     => $shop->state,
        //                 'route'    =>   $shop->type == ShopTypeEnum::FULFILMENT
        //                 ? [
        //                     'name'       => 'grp.org.fulfilments.show.dashboard',
        //                     'parameters' => [
        //                         'organisation' => $organisation->slug,
        //                         'fulfilment'   => $shop->slug
        //                     ]
        //                 ]
        //                 : [
        //                     'name'       => 'grp.org.shops.show.dashboard',
        //                     'parameters' => [
        //                         'organisation' => $organisation->slug,
        //                         'shop'         => $shop->slug
        //                     ]
        //                 ]
        //             ];

        //             if ($shop->salesIntervals !== null) {
        //                 $responseData['interval_percentages']['sales'] = $this->mapIntervals(
        //                     $shop->salesIntervals,
        //                     'sales',
        //                     [
        //                         'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
        //                     ]
        //                 );
        //             }

        //             if ($shop->orderingIntervals !== null) {
        //                 $responseData['interval_percentages']['invoices'] = $this->mapIntervals(
        //                     $shop->orderingIntervals,
        //                     'invoices',
        //                     [
        //                         'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
        //                     ]
        //                 );
        //             }

        //             if ($shop->orderingIntervals !== null) {
        //                 $responseData['interval_percentages']['refunds'] = $this->mapIntervals(
        //                     $shop->orderingIntervals,
        //                     'refunds',
        //                     [
        //                         'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
        //                     ]
        //                 );
        //             }

        //             return $responseData;
        //         }),
        // ];

        $userSettings = auth()->user()->settings;
        return Inertia::render(
            'Dashboard/OrganisationDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters(), __('Dashboard')),
                'dashboard_stats' => $this->getDashboardInterval($organisation, $userSettings),
            ]
        );
    }

    public function getDashboardInterval(Organisation $organisation, array $userSettings): array
    {
        $selectedInterval = Arr::get($userSettings, 'selected_interval', 'all');
        $shops = $organisation->shops;
        $orgCurrencies = [];
        foreach ($shops as $shop) {
            $orgCurrencies[] = $shop->currency->symbol;
        }
        $orgCurrenciesSymbol = implode('/', array_unique($orgCurrencies));

        $dashboard = [
            'interval_options'  => $this->getIntervalOptions(),
            'settings' => [
                'db_settings'   => auth()->user()->settings,
                'key_currency'  =>  'org',
                'options_currency'  => [
                    [
                        'value' => 'org',
                        'label' => $organisation->currency->symbol,
                    ],
                    [
                        'value' => 'shop',
                        'label' => $orgCurrenciesSymbol,
                    ]
                ]
            ],
            'table' => [],
            'widgets' => [
                'column_count'    => 2,
                'components' => []
            ]
        ];

        $selectedCurrency = Arr::get($userSettings, 'selected_currency_in_org', 'org');

        $total = [
            'total_sales'    => $organisation->salesIntervals?->{"sales_org_currency_{$selectedInterval}"},
            'total_invoices' => 0,
            'total_refunds'  => 0,
        ];

        $visualData = [
            'sales' => [],
            'invoices' => [],
        ];

        $dashboard['table'] = $shops->map(function (Shop $shop) use ($selectedInterval, $organisation, &$dashboard, $selectedCurrency, &$visualData, &$total) {
            $keyCurrency = $dashboard['settings']['key_currency'];
            $currencyCode = $selectedCurrency === $keyCurrency ? $organisation->currency->code : $shop->currency->code;
            $responseData = [
                'name'      => $shop->name,
                'slug'      => $shop->slug,
                'code'      => $shop->code,
                'type'      => $shop->type,
                'currency_code'  => $currencyCode,
                'state'     => $shop->state,
                'route'     => $shop->type == ShopTypeEnum::FULFILMENT
            ];

            if ($shop->salesIntervals !== null) {
                $responseData['interval_percentages']['sales'] = $this->getIntervalPercentage(
                    $shop->salesIntervals,
                    'sales_shop_currency',
                    $selectedInterval,
                );
                $visualData['sales_data']['labels'][] = $shop->code;
                $visualData['sales_data']['currency_codes'][] = $currencyCode;
                $visualData['sales_data']['datasets'][0]['data'][] = $responseData['interval_percentages']['sales']['amount'];
            }

            if ($shop->orderingIntervals !== null) {
                $responseData['interval_percentages']['invoices'] = $this->getIntervalPercentage(
                    $shop->orderingIntervals,
                    'invoices',
                    $selectedInterval,
                );
                $responseData['interval_percentages']['refunds'] = $this->getIntervalPercentage(
                    $shop->orderingIntervals,
                    'refunds',
                    $selectedInterval,
                );
                $total['total_invoices'] += $responseData['interval_percentages']['invoices']['amount'];
                $total['total_refunds'] += $responseData['interval_percentages']['refunds']['amount'];
                $visualData['invoices_data']['labels'][] = $shop->code;
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
                'currency_code' => $organisation->currency->code,
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

    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

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
