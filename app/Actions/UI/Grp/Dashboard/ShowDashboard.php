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
        $group = Group::first();
        $testOrg = $group->organisations->skip(1)->first();
        $sales = [
            'sales'         => JsonResource::make($group->salesStats),
            'currency'      => $group->currency,
            'organisations' => $group->organisations->map(function (Organisation $organisation) {
                // Initialize the response data
                $responseData = [
                    'name'      => $organisation->name,
                    'code'      => $organisation->code,
                    'type'      => $organisation->type,
                    'currency'  => $organisation->currency,
                    'sales'     => $organisation->salesIntervals,
                    'invoices'  => [
                        'number_invoices' => $organisation->salesStats->number_invoices_type_invoice ?? null
                    ],
                    'refunds' => [
                        'number_refunds' => $organisation->salesStats->number_invoices_type_refund ?? null
                    ]
                ];
                if ($organisation->salesIntervals !== null) {
                    $responseData['interval_percentages'] = [
                        'ytd' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_ytd, $organisation->salesIntervals->org_amount_ytd_ly),
                            'difference' => $organisation->salesIntervals->org_amount_ytd - $organisation->salesIntervals->org_amount_ytd_ly
                        ],
                        'qtd' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_qtd, $organisation->salesIntervals->org_amount_qtd_ly),
                            'difference' => $organisation->salesIntervals->org_amount_qtd - $organisation->salesIntervals->org_amount_qtd_ly
                        ],
                        'mtd' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_mtd, $organisation->salesIntervals->org_amount_mtd_ly),
                            'difference' => $organisation->salesIntervals->org_amount_mtd - $organisation->salesIntervals->org_amount_mtd_ly
                        ],
                        'wtd' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_wtd, $organisation->salesIntervals->org_amount_wtd_ly),
                            'difference' => $organisation->salesIntervals->org_amount_wtd - $organisation->salesIntervals->org_amount_wtd_ly
                        ],
                        'lm' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_lm, $organisation->salesIntervals->org_amount_lm_ly),
                            'difference' => $organisation->salesIntervals->org_amount_lm - $organisation->salesIntervals->org_amount_lm_ly
                        ],
                        'lw' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_lw, $organisation->salesIntervals->org_amount_lw_ly),
                            'difference' => $organisation->salesIntervals->org_amount_lw - $organisation->salesIntervals->org_amount_lw_ly
                        ],
                        'yda' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_yda, $organisation->salesIntervals->org_amount_yda_ly),
                            'difference' => $organisation->salesIntervals->org_amount_yda - $organisation->salesIntervals->org_amount_yda_ly
                        ],
                        'tdy' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_tdy, $organisation->salesIntervals->org_amount_tdy_ly),
                            'difference' => $organisation->salesIntervals->org_amount_tdy - $organisation->salesIntervals->org_amount_tdy_ly
                        ],
                        '1y' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_1y, $organisation->salesIntervals->org_amount_1y_ly),
                            'difference' => $organisation->salesIntervals->org_amount_1y - $organisation->salesIntervals->org_amount_1y_ly
                        ],
                        '1q' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_1q, $organisation->salesIntervals->org_amount_1q_ly),
                            'difference' => $organisation->salesIntervals->org_amount_1q - $organisation->salesIntervals->org_amount_1q_ly
                        ],
                        '1m' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_1m, $organisation->salesIntervals->org_amount_1m_ly),
                            'difference' => $organisation->salesIntervals->org_amount_1m - $organisation->salesIntervals->org_amount_1m_ly
                        ],
                        '1w' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_1w, $organisation->salesIntervals->org_amount_1w_ly),
                            'difference' => $organisation->salesIntervals->org_amount_1w - $organisation->salesIntervals->org_amount_1w_ly
                        ],
                        'all' => [
                            'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->org_amount_all, $organisation->salesIntervals->org_amount_all_ly),
                            'difference' => $organisation->salesIntervals->org_amount_all - $organisation->salesIntervals->org_amount_all_ly
                        ],
                    ];
                }
        
                return $responseData;
            })
        ];

        return Inertia::render(
            'Dashboard/Dashboard',
            [
                'breadcrumbs'       => $this->getBreadcrumbs(__('Dashboard')),
                'groupStats'        => $sales,
                'interval_options'  => [
                    [
                        'label'      => trans('Year to date'),
                        'labelShort' => trans('Ytd'),
                        'value'      => 'ytd'
                    ],
                    [
                        'label'      => trans('Quarter to date'),
                        'labelShort' => trans('Qtd'),
                        'value'      => 'qtd'
                    ],
                    [
                        'label'      => trans('Month to date'),
                        'labelShort' => trans('Mtd'),
                        'value'      => 'mtd'
                    ],
                    [
                        'label'      => trans('Week to date'),
                        'labelShort' => trans('Wtd'),
                        'value'      => 'wtd'
                    ],
                    [
                        'label'      => trans('Last month'),
                        'labelShort' => trans('lm'),
                        'value'      => 'lm'
                    ],
                    [
                        'label'      => trans('Last week'),
                        'labelShort' => trans('lw'),
                        'value'      => 'lw'
                    ],
                    [
                        'label'      => trans('Yesterday'),
                        'labelShort' => trans('y'),
                        'value'      => 'yda'
                    ],
                    [
                        'label'      => trans('Today'),
                        'labelShort' => trans('t'),
                        'value'      => 'tdy'
                    ],
                    [
                        'label'      => trans('1 Year'),
                        'labelShort' => trans('1y'),
                        'value'      => '1y'
                    ],
                    [
                        'label'      => trans('1 Quarter'),
                        'labelShort' => trans('1q'),
                        'value'      => '1q'
                    ],
                    [
                        'label'      => trans('1 Month'),
                        'labelShort' => trans('1m'),
                        'value'      => '1m'
                    ],
                    [
                        'label'      => trans('1 Week'),
                        'labelShort' => trans('1w'),
                        'value'      => '1w'
                    ],
                    [
                        'label'      => trans('All'),
                        'labelShort' => trans('All'),
                        'value'      => 'all'
                    ],
                ]
            ]
        );
    }

    public function getBreadcrumbs($label=null): array
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
            return $thisYear > 0 ? INF : 0;
        }

        $percentageIncrease = (($thisYear - $lastYear) / $lastYear) * 100;

        return $percentageIncrease;
    }
}
