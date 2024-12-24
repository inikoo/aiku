<?php

/*
 * author Arya Permana - Kirin
 * created on 24-12-2024-14h-17m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\SysAdmin\User;

use App\Models\SysAdmin\User;
use Lorisleiva\Actions\ActionRequest;

trait WithUserSubNavigation
{
    protected function getUserNavigation(User $user, ActionRequest $request): array
    {
        return [
            [
                "label"    => __("Visit Logs"),
                "route"     => [
                    "name"       => "grp.sysadmin.users.request.index",
                    "parameters" => [
                        'user' => $user->username
                    ],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-user"],
                    "tooltip" => __("Visit Logs"),
                ],
            ],
        ];
    }
}
