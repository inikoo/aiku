<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 05 Jan 2025 22:44:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Overview;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOrganisationOverviewHub extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('org-reports.'.$this->organisation->id);
    }

    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->organisation = $organisation;
        $this->initialisation($organisation, $request);

        return $request;
    }

    public function htmlResponse(ActionRequest $request): Response
    {
        $routeName       = $request->route()->getName();
        $routeParameters = $request->route()->originalParameters();
        return Inertia::render(
            'Overview/OverviewHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
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
                                    'data' => GetOrganisationOverview::run($this->organisation)
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

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return
            array_merge(
                ShowOrganisationDashboard::make()->getBreadcrumbs(Arr::only($routeParameters, 'organisation')),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => $routeName,
                                'parameters' => $routeParameters
                            ],
                            'label'  => __('Overview'),
                        ]
                    ]
                ]
            );
    }
}
