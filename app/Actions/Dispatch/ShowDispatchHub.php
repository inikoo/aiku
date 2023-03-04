<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 12:00:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch;

use App\Actions\UI\WithInertia;
use App\Models\Central\Tenant;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property Tenant $tenant
 * @property User $user
 */
class ShowDispatchHub
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("osm.view");
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
            'Dispatch/DispatchHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => 'dispatch',
                'pageHead'    => [
                    'title' => __('Dispatch'),
                ],


            ]

        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'dispatch.hub' => [
                'route' => 'dispatch.hub',
                'name'  => __('dispatch'),
            ]
        ];
    }


}
