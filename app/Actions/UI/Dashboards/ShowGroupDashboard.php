<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Dec 2024 00:41:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dashboards;

use App\Actions\OrgAction;
use App\Actions\Traits\WithDashboard;
use App\Enums\DateIntervals\DateIntervalEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;

class ShowGroupDashboard extends OrgAction
{
    use WithDashboard;
    public function handle(Group $group): Response
    {

        $sales   = [
            'sales'    => JsonResource::make($group->orderingStats),
            'currency' => $group->currency,
            'total'    => collect(DateIntervalEnum::cases())->mapWithKeys(function ($interval) use ($group) {
                return [
                $interval->value => [
                    'total_sales'    => $group->organisations->sum(fn ($organisations) => $organisations->salesIntervals?->{"sales_org_currency_" . $interval->value} ?? 0),
                    'total_invoices' => $group->organisations->sum(fn ($organisations) => $organisations->orderingIntervals?->{"invoices_{$interval->value}"} ?? 0),
                    'total_refunds'  => $group->organisations->sum(fn ($organisations) => $organisations->orderingIntervals?->{"refunds_{$interval->value}"} ?? 0),
                ]
                ];
            })->toArray() + [
                'all' => [
                    'total_sales'    => $group->salesIntervals?->sales_grp_currency_all ?? 0,
                    'total_invoices' => $group->orderingIntervals?->invoices_all ?? 0,
                    'total_refunds'  => $group->orderingIntervals?->refunds_all ?? 0,
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
                        DateIntervalEnum::values(),
                    );
                }

                if ($organisation->orderingIntervals !== null) {
                    $responseData['interval_percentages']['invoices'] = $this->mapIntervals(
                        $organisation->orderingIntervals,
                        'invoices',
                        DateIntervalEnum::values(),
                    );
                }

                if ($organisation->orderingIntervals !== null) {
                    $responseData['interval_percentages']['refunds'] = $this->mapIntervals(
                        $organisation->orderingIntervals,
                        'refunds',
                        DateIntervalEnum::values(),
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
                'interval_options'  => $this->getIntervalOptions(),
                'dashboard_stats' => [
                    'interval_options'  => $this->getIntervalOptions(),
                    'settings' => auth()->user()->settings,
                    'widgets' => [
                        'column_count'    => 4,
                        'components'    => [
                            [
                                'type' => 'basic',
                                'col_span'  => 1,
                                'row_span'  => 2,
                                'data' => [
                                    'value'         => 0,
                                    'description'   => 'xxxxxxx',
                                    'status'    => 'success',
                                ]
                            ],
                            [
                                'type' => 'basic',
                                'col_span'  => 1,
                                'row_span'  => 1,
                                'data' => [
                                    'value'         => 180000,
                                    'description'   => 'ggggggg',
                                    'status'    => 'danger',
                                    'type'      => 'currency',
                                    'currency_code' => 'GBP'
                                ]
                            ],
                            [
                                'type' => 'basic',
                                'col_span'  => 1,
                                'row_span'  => 1,
                                'data' => [
                                    'value'         => 662137,
                                    'description'   => 'ggggggg',
                                    // 'status'    => 'information',
                                    'type'      => 'currency',
                                    'currency_code' => 'GBP'
                                ]
                            ],
                            [
                                'type' => 'basic',
                                'col_span'  => 1,
                                'row_span'  => 1,
                                'data' => [
                                    'value'         => 99,
                                    'type'      => 'number',
                                    'description'   => 'Hell owrodl',
                                    'status'    => 'warning',
                                ]
                            ],
                            [
                                'type' => 'basic',
                                'col_span'  => 3,
                                'row_span'  => 1,
                                'data' => [
                                    'value'         => 44400,
                                    'description'   => '6666',
                                    'status'    => 'information',
                                    // 'status'    => 'success',
                                ]
                            ],
                        ]
                    ],
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

    protected function mapIntervals($intervalData, string $prefix, array $keys): array
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
