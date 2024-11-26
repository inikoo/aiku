<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:11:09 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Dashboard;

use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDashboard
{
    use AsAction;

    public function handle(): Response
    {

        /** @var Group $group */
        $group   = Group::first();
        $testOrg = $group->organisations->skip(1)->first();
        $sales   = [
            'sales'         => JsonResource::make($group->orderingStats),
            'currency'      => $group->currency,
            'total'         => [
                'ytd' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_ytd ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_ytd ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_ytd ?? 0;
                    }),
                ],
                'qtd' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_qtd ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_qtd ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_qtd ?? 0;
                    }),
                ],
                'mtd' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_mtd ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_mtd ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_mtd ?? 0;
                    }),
                ],
                'wtd' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_wtd ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_wtd ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_wtd ?? 0;
                    }),
                ],
                'lm' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_lm ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_lm ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_lm ?? 0;
                    }),
                ],
                'lw' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_lw ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_lw ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_lw ?? 0;
                    }),
                ],
                'yda' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_yda ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_yda ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_yda ?? 0;
                    }),
                ],
                'tdy' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_tdy ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_tdy ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_tdy ?? 0;
                    }),
                ],
                '1y' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_1y ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_1y ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_1y ?? 0;
                    }),
                ],
                '1q' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_1q ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_1q ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_1q ?? 0;
                    }),
                ],
                '1m' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_1m ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_1m ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_1m ?? 0;
                    }),
                ],
                '1w' => [
                    'total_invoices' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->invoices_1w ?? 0;
                    }),
                    'total_refunds' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->refunds_1w ?? 0;
                    }),
                    'total_sales' => $group->organisations->sum(function ($organisation) {
                        return $organisation->salesIntervals->sales_org_currency_1w ?? 0;
                    }),
                ],
                'all' => [
                    'total_invoices' => $group->orderingStats->number_invoices_type_invoice,
                    'total_refunds'  => $group->orderingStats->number_invoices_type_refund,
                    'total_sales'    => $group->salesIntervals->sales_grp_currency_all
                ]
            ],
            'organisations' => $group->organisations->map(function (Organisation $organisation) {
                // Initialize the response data
                $responseData = [
                    'name'      => $organisation->name,
                    'code'      => $organisation->code,
                    'type'      => $organisation->type,
                    'currency'  => $organisation->currency,
                    'sales'     => $organisation->salesIntervals,
                    // 'invoices'  => [
                    //     'number_invoices' => $organisation->salesStats->number_invoices_type_invoice ?? null
                    // ],
                    // 'refunds' => [
                    //     'number_refunds' => $organisation->salesStats->number_invoices_type_refund ?? null
                    // ],
                ];

                if ($organisation->salesIntervals !== null) {
                    $responseData['interval_percentages'] = [
                            'sales' => [
                                        'ytd' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_ytd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_ytd, $organisation->salesIntervals->sales_org_currency_ytd_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_ytd - $organisation->salesIntervals->sales_org_currency_ytd_ly
                                        ],
                                        'qtd' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_qtd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_qtd, $organisation->salesIntervals->sales_org_currency_qtd_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_qtd - $organisation->salesIntervals->sales_org_currency_qtd_ly
                                        ],
                                        'mtd' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_mtd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_mtd, $organisation->salesIntervals->sales_org_currency_mtd_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_mtd - $organisation->salesIntervals->sales_org_currency_mtd_ly
                                        ],
                                        'wtd' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_wtd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_wtd, $organisation->salesIntervals->sales_org_currency_wtd_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_wtd - $organisation->salesIntervals->sales_org_currency_wtd_ly
                                        ],
                                        'lm' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_lm,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_lm, $organisation->salesIntervals->sales_org_currency_lm_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_lm - $organisation->salesIntervals->sales_org_currency_lm_ly
                                        ],
                                        'lw' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_lw,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_lw, $organisation->salesIntervals->sales_org_currency_lw_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_lw - $organisation->salesIntervals->sales_org_currency_lw_ly
                                        ],
                                        'yda' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_yda,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_yda, $organisation->salesIntervals->sales_org_currency_yda_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_yda - $organisation->salesIntervals->sales_org_currency_yda_ly
                                        ],
                                        'tdy' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_tdy,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_tdy, $organisation->salesIntervals->sales_org_currency_tdy_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_tdy - $organisation->salesIntervals->sales_org_currency_tdy_ly
                                        ],
                                        '1y' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_1y,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_1y, $organisation->salesIntervals->sales_org_currency_1y_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_1y - $organisation->salesIntervals->sales_org_currency_1y_ly
                                        ],
                                        '1q' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_1q,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_1q, $organisation->salesIntervals->sales_org_currency_1q_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_1q - $organisation->salesIntervals->sales_org_currency_1q_ly
                                        ],
                                        '1m' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_1m,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_1m, $organisation->salesIntervals->sales_org_currency_1m_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_1m - $organisation->salesIntervals->sales_org_currency_1m_ly
                                        ],
                                        '1w' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_1w,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_1w, $organisation->salesIntervals->sales_org_currency_1w_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_1w - $organisation->salesIntervals->sales_org_currency_1w_ly
                                        ],
                                        'all' => [
                                            'amount'     => $organisation->salesIntervals->sales_org_currency_all,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->sales_org_currency_all, $organisation->salesIntervals->sales_org_currency_all_ly),
                                            'difference' => $organisation->salesIntervals->sales_org_currency_all - $organisation->salesIntervals->sales_org_currency_all_ly
                                        ],
                                    ],
                        'invoices' => [
                                        'ytd' => [
                                        'amount'     => $organisation->salesIntervals->invoices_ytd,
                                        'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_ytd, $organisation->salesIntervals->invoices_ytd_ly),
                                        'difference' => $organisation->salesIntervals->invoices_ytd - $organisation->salesIntervals->invoices_ytd_ly
                                        ],
                                        'qtd' => [
                                            'amount'     => $organisation->salesIntervals->invoices_qtd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_qtd, $organisation->salesIntervals->invoices_qtd_ly),
                                            'difference' => $organisation->salesIntervals->invoices_qtd - $organisation->salesIntervals->invoices_qtd_ly
                                        ],
                                        'mtd' => [
                                            'amount'     => $organisation->salesIntervals->invoices_mtd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_mtd, $organisation->salesIntervals->invoices_mtd_ly),
                                            'difference' => $organisation->salesIntervals->invoices_mtd - $organisation->salesIntervals->invoices_mtd_ly
                                        ],
                                        'wtd' => [
                                            'amount'     => $organisation->salesIntervals->invoices_wtd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_wtd, $organisation->salesIntervals->invoices_wtd_ly),
                                            'difference' => $organisation->salesIntervals->invoices_wtd - $organisation->salesIntervals->invoices_wtd_ly
                                        ],
                                        'lm' => [
                                            'amount'     => $organisation->salesIntervals->invoices_lm,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_lm, $organisation->salesIntervals->invoices_lm_ly),
                                            'difference' => $organisation->salesIntervals->invoices_lm - $organisation->salesIntervals->invoices_lm_ly
                                        ],
                                        'lw' => [
                                            'amount'     => $organisation->salesIntervals->invoices_lw,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_lw, $organisation->salesIntervals->invoices_lw_ly),
                                            'difference' => $organisation->salesIntervals->invoices_lw - $organisation->salesIntervals->invoices_lw_ly
                                        ],
                                        'yda' => [
                                            'amount'     => $organisation->salesIntervals->invoices_yda,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_yda, $organisation->salesIntervals->invoices_yda_ly),
                                            'difference' => $organisation->salesIntervals->invoices_yda - $organisation->salesIntervals->invoices_yda_ly
                                        ],
                                        'tdy' => [
                                            'amount'     => $organisation->salesIntervals->invoices_tdy,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_tdy, $organisation->salesIntervals->invoices_tdy_ly),
                                            'difference' => $organisation->salesIntervals->invoices_tdy - $organisation->salesIntervals->invoices_tdy_ly
                                        ],
                                        '1y' => [
                                            'amount'     => $organisation->salesIntervals->invoices_1y,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_1y, $organisation->salesIntervals->invoices_1y_ly),
                                            'difference' => $organisation->salesIntervals->invoices_1y - $organisation->salesIntervals->invoices_1y_ly
                                        ],
                                        '1q' => [
                                            'amount'     => $organisation->salesIntervals->invoices_1q,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_1q, $organisation->salesIntervals->invoices_1q_ly),
                                            'difference' => $organisation->salesIntervals->invoices_1q - $organisation->salesIntervals->invoices_1q_ly
                                        ],
                                        '1m' => [
                                            'amount'     => $organisation->salesIntervals->invoices_1m,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_1m, $organisation->salesIntervals->invoices_1m_ly),
                                            'difference' => $organisation->salesIntervals->invoices_1m - $organisation->salesIntervals->invoices_1m_ly
                                        ],
                                        '1w' => [
                                            'amount'     => $organisation->salesIntervals->invoices_1w,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_1w, $organisation->salesIntervals->invoices_1w_ly),
                                            'difference' => $organisation->salesIntervals->invoices_1w - $organisation->salesIntervals->invoices_1w_ly
                                        ],
                                        'all' => [
                                            'amount'     => $organisation->salesIntervals->invoices_all,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_all, $organisation->salesIntervals->invoices_all_ly),
                                            'difference' => $organisation->salesIntervals->invoices_all - $organisation->salesIntervals->invoices_all_ly
                                        ],
                                    ],
                        'refunds' => [
                                        'ytd' => [
                                        'amount'     => $organisation->salesIntervals->refunds_ytd,
                                        'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_ytd, $organisation->salesIntervals->refunds_ytd_ly),
                                        'difference' => $organisation->salesIntervals->refunds_ytd - $organisation->salesIntervals->refunds_ytd_ly
                                        ],
                                        'qtd' => [
                                            'amount'     => $organisation->salesIntervals->refunds_qtd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_qtd, $organisation->salesIntervals->refunds_qtd_ly),
                                            'difference' => $organisation->salesIntervals->refunds_qtd - $organisation->salesIntervals->refunds_qtd_ly
                                        ],
                                        'mtd' => [
                                            'amount'     => $organisation->salesIntervals->refunds_mtd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_mtd, $organisation->salesIntervals->refunds_mtd_ly),
                                            'difference' => $organisation->salesIntervals->refunds_mtd - $organisation->salesIntervals->refunds_mtd_ly
                                        ],
                                        'wtd' => [
                                            'amount'     => $organisation->salesIntervals->refunds_wtd,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_wtd, $organisation->salesIntervals->refunds_wtd_ly),
                                            'difference' => $organisation->salesIntervals->refunds_wtd - $organisation->salesIntervals->refunds_wtd_ly
                                        ],
                                        'lm' => [
                                            'amount'     => $organisation->salesIntervals->refunds_lm,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_lm, $organisation->salesIntervals->refunds_lm_ly),
                                            'difference' => $organisation->salesIntervals->refunds_lm - $organisation->salesIntervals->refunds_lm_ly
                                        ],
                                        'lw' => [
                                            'amount'     => $organisation->salesIntervals->refunds_lw,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_lw, $organisation->salesIntervals->refunds_lw_ly),
                                            'difference' => $organisation->salesIntervals->refunds_lw - $organisation->salesIntervals->refunds_lw_ly
                                        ],
                                        'yda' => [
                                            'amount'     => $organisation->salesIntervals->refunds_yda,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_yda, $organisation->salesIntervals->refunds_yda_ly),
                                            'difference' => $organisation->salesIntervals->refunds_yda - $organisation->salesIntervals->refunds_yda_ly
                                        ],
                                        'tdy' => [
                                            'amount'     => $organisation->salesIntervals->refunds_tdy,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_tdy, $organisation->salesIntervals->refunds_tdy_ly),
                                            'difference' => $organisation->salesIntervals->refunds_tdy - $organisation->salesIntervals->refunds_tdy_ly
                                        ],
                                        '1y' => [
                                            'amount'     => $organisation->salesIntervals->refunds_1y,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_1y, $organisation->salesIntervals->refunds_1y_ly),
                                            'difference' => $organisation->salesIntervals->refunds_1y - $organisation->salesIntervals->refunds_1y_ly
                                        ],
                                        '1q' => [
                                            'amount'     => $organisation->salesIntervals->refunds_1q,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_1q, $organisation->salesIntervals->refunds_1q_ly),
                                            'difference' => $organisation->salesIntervals->refunds_1q - $organisation->salesIntervals->refunds_1q_ly
                                        ],
                                        '1m' => [
                                            'amount'     => $organisation->salesIntervals->refunds_1m,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_1m, $organisation->salesIntervals->refunds_1m_ly),
                                            'difference' => $organisation->salesIntervals->refunds_1m - $organisation->salesIntervals->refunds_1m_ly
                                        ],
                                        '1w' => [
                                            'amount'     => $organisation->salesIntervals->refunds_1w,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_1w, $organisation->salesIntervals->refunds_1w_ly),
                                            'difference' => $organisation->salesIntervals->refunds_1w - $organisation->salesIntervals->refunds_1w_ly
                                        ],
                                        'all' => [
                                            'amount'     => $organisation->salesIntervals->refunds_all,
                                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_all, $organisation->salesIntervals->refunds_all_ly),
                                            'difference' => $organisation->salesIntervals->refunds_all - $organisation->salesIntervals->refunds_all_ly
                                        ],
                                    ]

                    ];
                }

                return $responseData;
            })
        ];
        return Inertia::render(
            'Dashboard/GrpDashboard',
            [
                'breadcrumbs'       => $this->getBreadcrumbs(__('Dashboard')),
                'groupStats'        => $sales,
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
                        'labelShort' => __('y'),
                        'value'      => 'yda'
                    ],
                    [
                        'label'      => __('Today'),
                        'labelShort' => __('t'),
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
                ]
            ]
        );
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

    public function calculatePercentageIncrease($thisYear, $lastYear)
    {
        if ($lastYear == 0) {
            return $thisYear > 0 ? null : 0;
        }

        $percentageIncrease = (($thisYear - $lastYear) / $lastYear) * 100;

        return $percentageIncrease;
    }
}
