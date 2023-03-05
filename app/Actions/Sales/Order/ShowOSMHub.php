<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 12:00:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Order;

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
class ShowOSMHub
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
            'Sales/OSMHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => 'OSM',
                'pageHead'    => [
                    'title' => __('Order service management'),
                ],


            ]
        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'osm.hub' => [
                'route' => 'osm.hub',
                'name'  => 'osm',
            ]
        ];
    }
}
