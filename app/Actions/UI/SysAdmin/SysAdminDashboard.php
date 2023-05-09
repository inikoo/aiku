<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:48:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\SysAdmin;

use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class SysAdminDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("sysadmin.view");
    }


    public function asController(): bool
    {
        return true;
    }


    public function htmlResponse(): Response
    {
        $tenant=app('currentTenant');

        return Inertia::render(
            'SysAdmin/SysAdminDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('system administration'),
                'pageHead'    => [
                    'title' => __('system administration'),
                ],
                'stats' => [
                    [
                        'name' => __('users'),
                        'stat' => $tenant->stats->number_users_status_active,
                        'href' => ['sysadmin.users.index']
                    ],
                    [
                        'name' => __('guests'),
                        'stat' => $tenant->stats->number_guests_status_active,
                        'href' => ['sysadmin.guests.index']
                    ]
                ]

            ]
        );
    }



    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'sysadmin.dashboard'
                            ],
                            'label'  => __('system administration'),
                        ]
                    ]
                ]
            );
    }
}
