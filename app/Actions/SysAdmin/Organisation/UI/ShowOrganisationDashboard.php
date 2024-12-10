<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:40:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Actions\OrgAction;
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
            'sales'         => JsonResource::make($organisation->orderingStats),
            'currency'      => $organisation->currency,
            'total'         => [
                'ytd' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_ytd ?? 0;
                    }),
                ],
                'qtd' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_qtd ?? 0;
                    }),
                ],
                'mtd' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_mtd ?? 0;
                    }),
                ],
                'wtd' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_wtd ?? 0;
                    }),
                ],
                'lm' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_lm ?? 0;
                    }),
                ],
                'lw' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_lw ?? 0;
                    }),
                ],
                'yda' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_yda ?? 0;
                    }),
                ],
                'tdy' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_tdy ?? 0;
                    }),
                ],
                '1y' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_1y ?? 0;
                    }),
                ],
                '1q' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_1q ?? 0;
                    }),
                ],
                '1m' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_1m ?? 0;
                    }),
                ],
                '1w' => [
                    'total_sales' => $organisation->shops->sum(function ($shop) {
                        return $shop->salesIntervals->sales_1w ?? 0;
                    }),
                ],
                'all' => [
                    'total_sales'    => $organisation->salesIntervals->sales_org_currency_all
                ]
            ],
            'shops' => $organisation->shops->map(function (Shop $shop) {
                // Initialize the response data
                $responseData = [
                    'name'      => $shop->name,
                    'slug'      => $shop->slug,
                    'type'      => $shop->type,
                    'currency'  => $shop->currency,
                    'sales'     => $shop->salesIntervals,
                    // 'invoices'  => [
                    //     'number_invoices' => $organisation->salesStats->number_invoices_type_invoice ?? null
                    // ],
                    // 'refunds' => [
                    //     'number_refunds' => $organisation->salesStats->number_invoices_type_refund ?? null
                    // ],
                ];

                if ($shop->salesIntervals !== null) {
                    $responseData['interval_percentages'] = [
                            'sales' => [
                                        'ytd' => [
                                            'amount'     => $shop->salesIntervals->sales_ytd,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_ytd, $shop->salesIntervals->sales_ytd_ly),
                                            'difference' => $shop->salesIntervals->sales_ytd - $shop->salesIntervals->sales_ytd_ly
                                        ],
                                        'qtd' => [
                                            'amount'     => $shop->salesIntervals->sales_qtd,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_qtd, $shop->salesIntervals->sales_qtd_ly),
                                            'difference' => $shop->salesIntervals->sales_qtd - $shop->salesIntervals->sales_qtd_ly
                                        ],
                                        'mtd' => [
                                            'amount'     => $shop->salesIntervals->sales_mtd,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_mtd, $shop->salesIntervals->sales_mtd_ly),
                                            'difference' => $shop->salesIntervals->sales_mtd - $shop->salesIntervals->sales_mtd_ly
                                        ],
                                        'wtd' => [
                                            'amount'     => $shop->salesIntervals->sales_wtd,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_wtd, $shop->salesIntervals->sales_wtd_ly),
                                            'difference' => $shop->salesIntervals->sales_wtd - $shop->salesIntervals->sales_wtd_ly
                                        ],
                                        'lm' => [
                                            'amount'     => $shop->salesIntervals->sales_lm,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_lm, $shop->salesIntervals->sales_lm_ly),
                                            'difference' => $shop->salesIntervals->sales_lm - $shop->salesIntervals->sales_lm_ly
                                        ],
                                        'lw' => [
                                            'amount'     => $shop->salesIntervals->sales_lw,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_lw, $shop->salesIntervals->sales_lw_ly),
                                            'difference' => $shop->salesIntervals->sales_lw - $shop->salesIntervals->sales_lw_ly
                                        ],
                                        'yda' => [
                                            'amount'     => $shop->salesIntervals->sales_yda,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_yda, $shop->salesIntervals->sales_yda_ly),
                                            'difference' => $shop->salesIntervals->sales_yda - $shop->salesIntervals->sales_yda_ly
                                        ],
                                        'tdy' => [
                                            'amount'     => $shop->salesIntervals->sales_tdy,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_tdy, $shop->salesIntervals->sales_tdy_ly),
                                            'difference' => $shop->salesIntervals->sales_tdy - $shop->salesIntervals->sales_tdy_ly
                                        ],
                                        '1y' => [
                                            'amount'     => $shop->salesIntervals->sales_1y,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_1y, $shop->salesIntervals->sales_1y_ly),
                                            'difference' => $shop->salesIntervals->sales_1y - $shop->salesIntervals->sales_1y_ly
                                        ],
                                        '1q' => [
                                            'amount'     => $shop->salesIntervals->sales_1q,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_1q, $shop->salesIntervals->sales_1q_ly),
                                            'difference' => $shop->salesIntervals->sales_1q - $shop->salesIntervals->sales_1q_ly
                                        ],
                                        '1m' => [
                                            'amount'     => $shop->salesIntervals->sales_1m,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_1m, $shop->salesIntervals->sales_1m_ly),
                                            'difference' => $shop->salesIntervals->sales_1m - $shop->salesIntervals->sales_1m_ly
                                        ],
                                        '1w' => [
                                            'amount'     => $shop->salesIntervals->sales_1w,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_1w, $shop->salesIntervals->sales_1w_ly),
                                            'difference' => $shop->salesIntervals->sales_1w - $shop->salesIntervals->sales_1w_ly
                                        ],
                                        'all' => [
                                            'amount'     => $shop->salesIntervals->sales_all,
                                            'percentage' => $this->calculatePercentageIncrease($shop->salesIntervals->sales_all, $shop->salesIntervals->sales_all_ly),
                                            'difference' => $shop->salesIntervals->sales_all - $shop->salesIntervals->sales_all_ly
                                        ],
                                    ],
                    ];
                }

                return $responseData;
            })
        ];
        return Inertia::render(
            'Dashboard/OrganisationDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters(), __('Dashboard')),
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

    public function calculatePercentageIncrease($thisYear, $lastYear): ?float
    {
        if ($lastYear == 0) {
            return $thisYear > 0 ? null : 0;
        }

        return (($thisYear - $lastYear) / $lastYear) * 100;
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
