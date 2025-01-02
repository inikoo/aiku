<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 14:15:22 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\UI;

use App\Actions\GrpAction;
use App\Actions\SysAdmin\Group\GetOverview;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOverviewHub extends GrpAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("group-overview");
    }

    public function asController(ActionRequest $request): ActionRequest
    {
        $this->initialisation(app('group'), $request);
        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Overview/OverviewHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('overview'),
                'pageHead'    => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-mountains'],
                        'title' => __('overview')
                    ],
                    'title'     => __('overview'),
                ],
                'dashboard' => [
                    'setting' => [
                        "currency_choosen" => 'usd' // | pounds | dollar
                    ],
                    'columns' => [
                        [
                            'widgets' => [
                                [
                                    'type' => 'overview_table',
                                    'data' => GetOverview::run($this->group)
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

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowGroupDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.overview.hub'
                            ],
                            'label'  => __('Overview'),
                        ]
                    ]
                ]
            );
    }
}
