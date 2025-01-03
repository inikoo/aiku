<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Dec 2024 00:41:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dashboards;

use App\Actions\OrgAction;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;

class ShowGroupDashboard extends OrgAction
{
    public function handle(Group $group): Response
    {


        $sales   = [
            'sales'    => JsonResource::make($group->orderingStats),
            'currency' => $group->currency,
            'total'    => collect([
                'ytd' => 'sales_org_currency_ytd',
                'qtd' => 'sales_org_currency_qtd',
                'mtd' => 'sales_org_currency_mtd',
                'wtd' => 'sales_org_currency_wtd',
                'lm'  => 'sales_org_currency_lm',
                'lw'  => 'sales_org_currency_lw',
                'ld'  => 'sales_org_currency_ld',
                'tdy' => 'sales_org_currency_tdy',
                '1y'  => 'sales_org_currency_1y',
                '1q'  => 'sales_org_currency_1q',
                '1m'  => 'sales_org_currency_1m',
                '1w'  => 'sales_org_currency_1w',
            ])->mapWithKeys(function ($salesInterval, $key) use ($group) {
                return [
                    $key => [
                        'total_sales'    => $group->organisations->sum(fn ($organisations) => $organisations->salesIntervals->$salesInterval ?? 0),
                        'total_invoices' => $group->organisations->sum(fn ($organisations) => $organisations->orderingIntervals->{"invoices_{$key}"} ?? 0),
                        'total_refunds'  => $group->organisations->sum(fn ($organisations) => $organisations->orderingIntervals->{"refunds_{$key}"} ?? 0),
                    ]
                ];
            })->toArray() + [
                'all' => [
                    'total_sales'    => $group->salesIntervals->sales_grp_currency_all ?? 0,
                    'total_invoices' => $group->orderingIntervals->invoices_all ?? 0,
                    'total_refunds'  => $group->orderingIntervals->refunds_all ?? 0,
                ],
            ],
            'organisations' => $group->organisations->map(function (Organisation $organisation) {
                $responseData = [
                    'name'      => $organisation->name,
                    'slug'      => $organisation->slug,
                    'code'      => $organisation->code,
                    'type'      => $organisation->type,
                    'currency'  => $organisation->currency,
                ];

                if ($organisation->salesIntervals !== null) {
                    $responseData['interval_percentages']['sales'] = $this->mapIntervals(
                        $organisation->salesIntervals,
                        'sales_org_currency',
                        [
                            'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
                        ]
                    );
                }

                if ($organisation->orderingIntervals !== null) {
                    $responseData['interval_percentages']['invoices'] = $this->mapIntervals(
                        $organisation->orderingIntervals,
                        'invoices',
                        [
                            'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
                        ]
                    );
                }

                if ($organisation->orderingIntervals !== null) {
                    $responseData['interval_percentages']['refunds'] = $this->mapIntervals(
                        $organisation->orderingIntervals,
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
                        'labelShort' => __('ly'),
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
                'dashboard_stats' => [
                    'settings' => auth()->user()->settings,
                    'columns' => [
                        [
                            'widgets' => [
                                [
                                    'type' => 'overview_table',
                                    'data' => $sales
                                ]
                            ]
                        ],
                        [
                            'widgets' => [
                                [
                                    'label' => __('the nutrition store'),
                                    'data' => [
                                        [
                                            'label' => __('total orders today'),
                                            'value' => 275,
                                            'type' => 'card_currency_success'
                                        ],
                                        [
                                            'label' => __('sales today'),
                                            'value' => 2345,
                                            'type' => 'card_currency'
                                        ]
                                    ],
                                    'type' => 'multi_card',
                                ],
                                [
                                    'label' => __('the yoga store'),
                                    'data' => [
                                        [
                                            'label' => __('ad spend this week'),
                                            'value' => 46,
                                            'type' => 'card_percentage'
                                        ],
                                        [
                                            'label' => __('sales today'),
                                            'value' => 2345,
                                            'type' => 'card_currency'
                                        ]
                                    ],
                                    'type' => 'multi_card',
                                ],
                                [
                                    'label' => __('ad spend this week'),
                                    'value' => 2345,
                                    'type' => 'card_currency',
                                ],
                                [
                                    'label' => __('card adbandoment rate'),
                                    'value' => 45,
                                    'type' => 'card_percentage',
                                ],
                                [
                                    'label' => __('the yoga store'),
                                    'data' => [
                                        'label' => __('Total newsletter subscribers'),
                                        'value' => 55700,
                                        'progress_bar' => [
                                            'value' => 55,
                                            'max' => 100,
                                            'color' => 'success',
                                        ],
                                    ],
                                    'type' => 'card_progress_bar',
                                ],
                            ],
                        ]
                    ]
                ],
            ]
        );
    }

    public function asController(): Response
    {
        $group = group();
        $this->initialisationFromGroup($group, []);
        return $this->handle($group);
    }


    public function calculatePercentageIncrease($thisYear, $lastYear): ?float
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
