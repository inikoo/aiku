<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 05 Jan 2025 21:37:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Overview;

use App\Actions\GrpAction;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowGroupOverviewHub extends GrpAction
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
                        "currency_chosen" => 'usd' // | pounds | dollar
                    ],
                    'columns' => [
                        [
                            'widgets' => [
                                [
                                    'type' => 'overview_table',
                                    'data' => GetGroupOverview::run($this->group)
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
                                    'label' => __('card abandonment rate'),
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
