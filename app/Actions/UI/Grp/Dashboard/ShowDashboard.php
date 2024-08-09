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
        $sales = [
            'sales'         => JsonResource::make($group->salesStats),
            'currency'      => $group->currency,
            'organisations' => $group->organisations->map(function (Organisation $organisation) {
                return [
                    'name'      => $organisation->name,
                    'code'      => $organisation->code,
                    'type'      => $organisation->type,
                    'currency'  => $organisation->currency,
                    'sales'     => $organisation->salesIntervals,
                    'invoices'  => [
                        'number_invoices' => $organisation->accountingStats->number_invoices_type_invoice ?? null
                    ],
                    'refunds' => [
                        'number_refunds' => $organisation->accountingStats->number_invoices_type_refund ?? null
                    ]
                ];
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
}
