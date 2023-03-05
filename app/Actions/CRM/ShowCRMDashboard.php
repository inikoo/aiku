<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 20:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM;

use App\Actions\UI\WithInertia;
use App\Models\Central\Tenant;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property Tenant $tenant
 * @property User $user
 */
class ShowCRMDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("crm.view");
    }


    public function asController(ActionRequest $request): void
    {
        $this->user   = $request->user();
        $this->tenant = app('currentTenant');
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();



        return Inertia::render(
            'CRM/CRMDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => 'CRM',
                'pageHead'    => [
                    'title' => __('customer relationship manager'),
                ],


            ]
        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'crm.hub' => [
                'route' => 'crm.dashboard',
                'name'  => 'crm',
            ]
        ];
    }
}
