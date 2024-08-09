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
                        'sales' => [
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
                                    ],
                        // 'invoices' => [
                        //                 'ytd' => [
                        //                 'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_ytd, $organisation->salesIntervals->invoices_ytd_ly),
                        //                 'difference' => $organisation->salesIntervals->invoices_ytd - $organisation->salesIntervals->invoices_ytd_ly
                        //                 ],
                        //                 'qtd' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_qtd, $organisation->salesIntervals->invoices_qtd_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_qtd - $organisation->salesIntervals->invoices_qtd_ly
                        //                 ],
                        //                 'mtd' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_mtd, $organisation->salesIntervals->invoices_mtd_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_mtd - $organisation->salesIntervals->invoices_mtd_ly
                        //                 ],
                        //                 'wtd' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_wtd, $organisation->salesIntervals->invoices_wtd_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_wtd - $organisation->salesIntervals->invoices_wtd_ly
                        //                 ],
                        //                 'lm' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_lm, $organisation->salesIntervals->invoices_lm_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_lm - $organisation->salesIntervals->invoices_lm_ly
                        //                 ],
                        //                 'lw' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_lw, $organisation->salesIntervals->invoices_lw_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_lw - $organisation->salesIntervals->invoices_lw_ly
                        //                 ],
                        //                 'yda' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_yda, $organisation->salesIntervals->invoices_yda_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_yda - $organisation->salesIntervals->invoices_yda_ly
                        //                 ],
                        //                 'tdy' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_tdy, $organisation->salesIntervals->invoices_tdy_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_tdy - $organisation->salesIntervals->invoices_tdy_ly
                        //                 ],
                        //                 '1y' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_1y, $organisation->salesIntervals->invoices_1y_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_1y - $organisation->salesIntervals->invoices_1y_ly
                        //                 ],
                        //                 '1q' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_1q, $organisation->salesIntervals->invoices_1q_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_1q - $organisation->salesIntervals->invoices_1q_ly
                        //                 ],
                        //                 '1m' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_1m, $organisation->salesIntervals->invoices_1m_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_1m - $organisation->salesIntervals->invoices_1m_ly
                        //                 ],
                        //                 '1w' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_1w, $organisation->salesIntervals->invoices_1w_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_1w - $organisation->salesIntervals->invoices_1w_ly
                        //                 ],
                        //                 'all' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->invoices_all, $organisation->salesIntervals->invoices_all_ly),
                        //                     'difference' => $organisation->salesIntervals->invoices_all - $organisation->salesIntervals->invoices_all_ly
                        //                 ],
                        //             ],
                        // 'refunds' => [
                        //                 'ytd' => [
                        //                 'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_ytd, $organisation->salesIntervals->refunds_ytd_ly),
                        //                 'difference' => $organisation->salesIntervals->refunds_ytd - $organisation->salesIntervals->refunds_ytd_ly
                        //                 ],
                        //                 'qtd' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_qtd, $organisation->salesIntervals->refunds_qtd_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_qtd - $organisation->salesIntervals->refunds_qtd_ly
                        //                 ],
                        //                 'mtd' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_mtd, $organisation->salesIntervals->refunds_mtd_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_mtd - $organisation->salesIntervals->refunds_mtd_ly
                        //                 ],
                        //                 'wtd' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_wtd, $organisation->salesIntervals->refunds_wtd_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_wtd - $organisation->salesIntervals->refunds_wtd_ly
                        //                 ],
                        //                 'lm' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_lm, $organisation->salesIntervals->refunds_lm_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_lm - $organisation->salesIntervals->refunds_lm_ly
                        //                 ],
                        //                 'lw' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_lw, $organisation->salesIntervals->refunds_lw_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_lw - $organisation->salesIntervals->refunds_lw_ly
                        //                 ],
                        //                 'yda' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_yda, $organisation->salesIntervals->refunds_yda_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_yda - $organisation->salesIntervals->refunds_yda_ly
                        //                 ],
                        //                 'tdy' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_tdy, $organisation->salesIntervals->refunds_tdy_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_tdy - $organisation->salesIntervals->refunds_tdy_ly
                        //                 ],
                        //                 '1y' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_1y, $organisation->salesIntervals->refunds_1y_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_1y - $organisation->salesIntervals->refunds_1y_ly
                        //                 ],
                        //                 '1q' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_1q, $organisation->salesIntervals->refunds_1q_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_1q - $organisation->salesIntervals->refunds_1q_ly
                        //                 ],
                        //                 '1m' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_1m, $organisation->salesIntervals->refunds_1m_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_1m - $organisation->salesIntervals->refunds_1m_ly
                        //                 ],
                        //                 '1w' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_1w, $organisation->salesIntervals->refunds_1w_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_1w - $organisation->salesIntervals->refunds_1w_ly
                        //                 ],
                        //                 'all' => [
                        //                     'percentage' => $this->calculatePercentageIncrease($organisation->salesIntervals->refunds_all, $organisation->salesIntervals->refunds_all_ly),
                        //                     'difference' => $organisation->salesIntervals->refunds_all - $organisation->salesIntervals->refunds_all_ly
                        //                 ],
                        //             ]

                    ];
                }
        
                return $responseData;
            })
        ];
        return Inertia::render(
            'Dashboard/Dashboard',
            [
                'breadcrumbs'      => $this->getBreadcrumbs(__('Dashboard')),
                'groupStats'       => $sales,
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
            return $thisYear > 0 ? null : 0;
        }

        $percentageIncrease = (($thisYear - $lastYear) / $lastYear) * 100;

        return $percentageIncrease;
    }
}
