<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\UserTabsEnum;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Auth\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowUser extends InertiaAction
{
    use HasUIUser;


    public function asController(User $user, ActionRequest $request): User
    {
        $this->initialisation($request)->withTab(UserTabsEnum::values());
        return $user;
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('sysadmin.users.edit');
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
                    'title'     => '@'.$user->username,
                    'edit'      => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'capitalize'=> false

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => UserTabsEnum::navigation()
                ]
            ]
        );
    }
}
