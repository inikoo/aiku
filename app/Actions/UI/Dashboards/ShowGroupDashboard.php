<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Dec 2024 00:41:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dashboards;

use App\Actions\OrgAction;
use App\Actions\Traits\WithDashboard;
use App\Models\SysAdmin\Group;
use Inertia\Inertia;
use Inertia\Response;

class ShowGroupDashboard extends OrgAction
{
    use WithDashboard;
    public function handle(Group $group): Response
    {

        $userSettings = auth()->user()->settings;
        return Inertia::render(
            'Dashboard/GrpDashboard',
            [
                'breadcrumbs'       => $this->getBreadcrumbs(__('Dashboard')),
                'dashboard_stats' => $this->getDashboardInterval($group, $userSettings),
            ]
        );
    }

    public function asController(): Response
    {
        $group = group();
        $this->initialisationFromGroup($group, []);
        return $this->handle($group);
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
