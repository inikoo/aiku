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
                'data' => GetOverview::run($this->group)
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
