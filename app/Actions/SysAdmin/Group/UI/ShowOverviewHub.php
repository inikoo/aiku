<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 14:15:22 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\UI;

use App\Actions\GrpAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
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
                    'title'     => __('overview'),
                ],

                'flatTreeMaps' => [
                    [
                                    [
                                        'name'         => __('providers'),
                                        'icon'         => ['fal', 'fa-cash-register'],
                                        'href'         => [
                                            'name'       => 'grp.overview.accounting.payment-service-providers.index',
                                            'parameters' => []
                                        ],
                                        'index'        => [
                                            'number' => $this->group->accountingStats->number_payment_service_providers
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
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.overview.hub'
                            ],
                            'label'  => __('overview'),
                        ]
                    ]
                ]
            );
    }
}
