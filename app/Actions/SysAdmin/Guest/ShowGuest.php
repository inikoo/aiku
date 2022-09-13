<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 01:24:36 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\SysAdmin\ShowSysAdminDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\SysAdmin\GuestResource;
use App\Models\SysAdmin\Guest;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;




class ShowGuest
{
    use AsAction;
    use WithInertia;




    public function asController(Guest $guest): Guest
    {
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

        $this->validateAttributes();
        return Inertia::render(
            'SysAdmin/Guest',
            [
                'title'=>__('guest'),
                'breadcrumbs' => $this->getBreadcrumbs($guest),
                'pageHead'=>[
                    'title'=>$guest->name,

                ],
                'guest'    => $guest
            ]
        );

    }




    public function getBreadcrumbs(Guest $guest): array
    {
        return array_merge(
            (new ShowSysAdminDashboard())->getBreadcrumbs(),
            [
                'sysadmin.guests.show' => [
                    'route'           => 'sysadmin.guests.show',
                    'routeParameters' => $guest->id,
                    'index'           => [
                        'route'   => 'sysadmin.guests.index',
                        'overlay' => __("guests' list")
                    ],
                    'modelLabel'      => [
                        'label' => __('guest')
                    ],
                    'name'            => $guest->code,

                ],
            ]
        );
    }


}
