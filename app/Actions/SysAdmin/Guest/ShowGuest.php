<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 01:24:36 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\InertiaAction;
use App\Actions\UI\SysAdmin\SysAdminDashboard;
use App\Enums\UI\GuestTabsEnum;
use App\Http\Resources\SysAdmin\GuestResource;
use App\Models\Auth\Guest;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowGuest extends InertiaAction
{
    public function asController(Guest $guest, ActionRequest $request): Guest
    {
        $this->initialisation($request)->withTab(GuestTabsEnum::values());
        return $guest;
    }

    public function jsonResponse(Guest $guest): GuestResource
    {
        return new GuestResource($guest);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function htmlResponse(Guest $guest): Response
    {
        return Inertia::render(
            'SysAdmin/Guest',
            [
                'title'       => __('guest'),
                'breadcrumbs' => $this->getBreadcrumbs($guest),
                'pageHead'    => [
                    'title' => $guest->name,

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => GuestTabsEnum::navigation()
                ]
            ]
        );
    }


    public function getBreadcrumbs(Guest $guest): array
    {
        return array_merge(
            (new SysAdminDashboard())->getBreadcrumbs(),
            [
                'sysadmin.guests.show' => [
                    'route'           => 'sysadmin.guests.show',
                    'routeParameters' => $guest->slug,
                    'index'           => [
                        'route'   => 'sysadmin.guests.index',
                        'overlay' => __("guests' list")
                    ],
                    'modelLabel'      => [
                        'label' => __('guest')
                    ],
                    'name'            => $guest->slug,

                ],
            ]
        );
    }
}
