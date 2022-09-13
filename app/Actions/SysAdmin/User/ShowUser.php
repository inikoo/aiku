<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 02:11:59 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\SysAdmin\ShowSysAdminDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Sysadmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


class ShowUser
{
    use AsAction;
    use WithInertia;


    public function asController(User $user): User
    {
        return $user;
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function htmlResponse(User $user): Response
    {
        $this->validateAttributes();

        return Inertia::render(
            'SysAdmin/User',
            [
                'title'       => __('user'),
                'breadcrumbs' => $this->getBreadcrumbs($user),
                'pageHead'    => [
                    'title' => '@'.$user->username,
                    'capitalize'=>false

                ],
                'user'        => $user
            ]
        );
    }

    public function getBreadcrumbs(User $user): array
    {
        return array_merge(
            (new ShowSysAdminDashboard())->getBreadcrumbs(),
            [
                'sysadmin.users.show' => [
                    'route'           => 'sysadmin.users.show',
                    'routeParameters' => $user->id,
                    'index'           => [
                        'route'   => 'sysadmin.users.index',
                        'overlay' => __("users' list")
                    ],
                    'modelLabel'      => [
                        'label' => __('user')
                    ],
                    'name'            => '@'.$user->username,

                ],
            ]
        );
    }


}
