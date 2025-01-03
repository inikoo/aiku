<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:40:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowOrganisationDashboard extends OrgAction
{
    use AsAction;

    public function handle(Organisation $organisation, ActionRequest $request): Response
    {
        $sales   = [
                'sales'    => JsonResource::make($organisation->orderingStats),
                'currency' => $organisation->currency,
                'total'    => collect([
                    'ytd' => 'sales_ytd',
                    'qtd' => 'sales_qtd',
                    'mtd' => 'sales_mtd',
                    'wtd' => 'sales_wtd',
                    'lm'  => 'sales_lm',
                    'lw'  => 'sales_lw',
                    'ld'  => 'sales_ld',
                    'tdy' => 'sales_tdy',
                    '1y'  => 'sales_1y',
                    '1q'  => 'sales_1q',
                    '1m'  => 'sales_1m',
                    '1w'  => 'sales_1w',
                ])->mapWithKeys(function ($salesInterval, $key) use ($organisation) {
                    return [
                        $key => [
                            'total_sales'    => $organisation->shops->sum(fn ($shop) => $shop->salesIntervals->$salesInterval ?? 0),
                            'total_invoices' => $organisation->shops->sum(fn ($shop) => $shop->orderingIntervals->{"invoices_{$key}"} ?? 0),
                            'total_refunds'  => $organisation->shops->sum(fn ($shop) => $shop->orderingIntervals->{"refunds_{$key}"} ?? 0),
                        ]
                    ];
                })->toArray() + [
                    'all' => [
                        'total_sales'    => $organisation->salesIntervals->sales_org_currency_all ?? 0,
                        'total_invoices' => $organisation->orderingIntervals->invoices_all ?? 0,
                        'total_refunds'  => $organisation->orderingIntervals->refunds_all ?? 0,
                    ],
                ],
                'shops' => $organisation->shops->map(function (Shop $shop) use ($organisation) {
                    $responseData = [
                        'name'      => $shop->name,
                        'slug'      => $shop->slug,
                        'type'      => $shop->type,
                        'currency'  => $shop->currency,
                        'state'     => $shop->state,
                        'route'    =>   $shop->type == ShopTypeEnum::FULFILMENT 
                        ? [
                            'name'       => 'grp.org.fulfilments.show.dashboard',
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
                        $responseData['interval_percentages']['sales'] = $this->mapIntervals(
                            $shop->salesIntervals,
                            'sales',
                            [
                                'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
                            ]
                        );
                    }

                    if ($shop->orderingIntervals !== null) {
                        $responseData['interval_percentages']['invoices'] = $this->mapIntervals(
                            $shop->orderingIntervals,
                            'invoices',
                            [
                                'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
                            ]
                        );
                    }

                    if ($shop->orderingIntervals !== null) {
                        $responseData['interval_percentages']['refunds'] = $this->mapIntervals(
                            $shop->orderingIntervals,
                            'refunds',
                            [
                                'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
                            ]
                        );
                    }

                    return $responseData;
                }),
        ];

        return Inertia::render(
            'Dashboard/OrganisationDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters(), __('Dashboard')),
                'interval_options'  => [
                    [
                        'label'      => __('Year to date'),
                        'labelShort' => __('Ytd'),
                        'value'      => 'ytd'
                    ],
                    [
                        'label'      => __('Quarter to date'),
                        'labelShort' => __('Qtd'),
                        'value'      => 'qtd'
                    ],
                    [
                        'label'      => __('Month to date'),
                        'labelShort' => __('Mtd'),
                        'value'      => 'mtd'
                    ],
                    [
                        'label'      => __('Week to date'),
                        'labelShort' => __('Wtd'),
                        'value'      => 'wtd'
                    ],
                    [
                        'label'      => __('Last month'),
                        'labelShort' => __('lm'),
                        'value'      => 'lm'
                    ],
                    [
                        'label'      => __('Last week'),
                        'labelShort' => __('lw'),
                        'value'      => 'lw'
                    ],
                    [
                        'label'      => __('Yesterday'),
                        'labelShort' => __('ld'),
                        'value'      => 'ld'
                    ],
                    [
                        'label'      => __('Today'),
                        'labelShort' => __('tdy'),
                        'value'      => 'tdy'
                    ],
                    [
                        'label'      => __('1 Year'),
                        'labelShort' => __('1y'),
                        'value'      => '1y'
                    ],
                    [
                        'label'      => __('1 Quarter'),
                        'labelShort' => __('1q'),
                        'value'      => '1q'
                    ],
                    [
                        'label'      => __('1 Month'),
                        'labelShort' => __('1m'),
                        'value'      => '1m'
                    ],
                    [
                        'label'      => __('1 Week'),
                        'labelShort' => __('1w'),
                        'value'      => '1w'
                    ],
                    [
                        'label'      => __('All'),
                        'labelShort' => __('All'),
                        'value'      => 'all'
                    ],
                ],
                'dashboard' => $sales


            ]
        );
    }

    // public function getDashboardData(Organisation $organisation): array
    // {
    //     $data = [];
    //     if ($organisation->type == OrganisationTypeEnum::SHOP) {
    //         $data = [
    //             'sales_intervals' => [
    //                 'all' => $organisation->salesIntervals->org_amount_all,
    //                 'ytd' => $organisation->salesIntervals->org_amount_ytd,
    //                 'mtd' => $organisation->salesIntervals->org_amount_mtd,
    //                 'lw'  => $organisation->salesIntervals->org_amount_lw,
    //                 'lm'  => $organisation->salesIntervals->org_amount_lm,
    //                 '1w'  => $organisation->salesIntervals->org_amount_1w,
    //                 '1m'  => $organisation->salesIntervals->org_amount_1m,
    //                 '1q'  => $organisation->salesIntervals->org_amount_1q,
    //                 '1y'  => $organisation->salesIntervals->org_amount_1y,
    //             ],
    //             'human_resources' => [
    //                 'job_positions' => $organisation->humanResourcesStats->number_job_positions,
    //                 'number_workplaces' => $organisation->humanResourcesStats->number_workplaces,
    //                 'number_clocking_machines' => $organisation->humanResourcesStats->number_clocking_machines,
    //                 'number_employees'  => $organisation->humanResourcesStats->number_employees,
    //                 'number_employees_currently_working'    => $organisation->humanResourcesStats->number_employees_currently_working
    //             ],
    //             'procurement'   => [
    //                 'number_org_agents' => $organisation->procurementStats->number_org_agents,
    //                 'number_org_suppliers' => $organisation->procurementStats->number_org_suppliers,
    //                 'number_purchase_orders' => $organisation->procurementStats->number_purchase_orders,
    //                 'number_stock_deliveries' => $organisation->procurementStats->number_stock_deliveries,
    //             ],
    //             'inventory' => [
    //                 'number_warehouses' =>  $organisation->inventoryStats->number_warehouses,
    //                 'number_locations'  =>  $organisation->inventoryStats->number_locations,
    //                 'number_empty_locations' => $organisation->inventoryStats->number_empty_locations,
    //                 'number_org_stocks' => $organisation->inventoryStats->number_org_stocks,
    //                 'number_deliveries' => $organisation->inventoryStats->number_deliveries
    //             ],
    //             'fulfilment'    => [
    //                 'number_pallets'    => $organisation->fulfilmentStats->number_pallets,
    //                 'number_stored_items'   => $organisation->fulfilmentStats->number_stored_items,
    //                 'number_pallet_deliveries'  => $organisation->fulfilmentStats->number_pallet_deliveries,
    //                 'number_recurring_bills'    => $organisation->fulfilmentStats->number_recurring_bills,
    //             ],
    //             'catalogue' => [
    //                 'number_departments'    => $organisation->catalogueStats->number_departments,
    //                 'number_collections'    => $organisation->catalogueStats->number_collections,
    //                 'number_assets'    => $organisation->catalogueStats->number_assets,
    //                 'number_products'    => $organisation->catalogueStats->number_products,
    //                 'number_services'    => $organisation->catalogueStats->number_services,
    //                 'number_subscriptions'    => $organisation->catalogueStats->number_subscriptions,
    //                 'number_charges'    => $organisation->catalogueStats->number_charges,
    //                 'number_shipping_zone_schemas'    => $organisation->catalogueStats->number_shipping_zone_schemas,
    //                 'number_shipping_zones'    => $organisation->catalogueStats->number_shipping_zones,
    //                 'number_adjustments'    => $organisation->catalogueStats->number_adjustments,
    //             ],
    //             'sales' => [
    //                 'number_orders' => $organisation->catalogueStats->number_orders,
    //                 'number_invoices'   =>  $organisation->catalogueStats->number_invoices,
    //                 'number_delivery_notes' => $organisation->catalogueStats->number_delivery_notes
    //             ]
    //             ];
    //     }

    //     return $data;
    // }

    protected function calculatePercentageIncrease($thisYear, $lastYear): ?float
    {
        if ($lastYear == 0) {
            return $thisYear > 0 ? null : 0;
        }

        return (($thisYear - $lastYear) / $lastYear) * 100;
    }

    protected function mapIntervals($intervalData, string $prefix, array $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = [
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
        }

        if (isset($result['all'])) {
            $result['all'] = [
                'amount' => $intervalData->{$prefix . '_all'} ?? null,
            ];
        }

        return $result;
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
