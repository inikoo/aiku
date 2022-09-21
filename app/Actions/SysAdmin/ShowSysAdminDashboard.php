<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 11 Sept 2022 00:49:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin;

use App\Actions\UI\WithInertia;
use App\Models\Central\Tenant;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;



class ShowSysAdminDashboard
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

        /** @var Tenant $tenant */
        $tenant=tenant();

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
                    ]
                ]

            ]

        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'hr.dashboard' => [
                'route' => 'sysadmin.dashboard',
                'name'  => __('system administration'),
            ]
        ];
    }


}
