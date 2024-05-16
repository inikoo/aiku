<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Actions\InertiaAction;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditUser extends InertiaAction
{
    public function handle(User $user): User
    {
        return $user;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function asController(User $user, ActionRequest $request): User
    {
        $this->initialisation($request);

        return $this->handle($user);
    }


    public function htmlResponse(User $user, ActionRequest $request): Response
    {
        $roles       = collect(RolesEnum::cases());
        $permissions = $roles->map(function ($role) {
            return [$role->label() => match ($role->scope()) {
                class_basename(Group::class) => Group::all()->map(function (Group $group) {
                    return [$group->name => [
                        'organisations' => $group->organisations->pluck('slug')
                    ]];
                }),
                class_basename(Organisation::class) => [
                    'organisations' => Organisation::all()->pluck('slug')
                ],
                class_basename(Shop::class) => Organisation::all()->map(function (Organisation $organisation) {
                    return [$organisation->name => [
                        'shops' => $organisation->shops->pluck('slug')
                    ]];
                }),
                class_basename(Fulfilment::class) => Organisation::all()->map(function (Organisation $organisation) {
                    return [$organisation->name => [
                        'fulfilments' => $organisation->fulfilments->pluck('slug')
                    ]];
                }),
                default => []
            }];
        });

        return Inertia::render("EditModel", [
            "title"       => __("user"),
            "breadcrumbs" => $this->getBreadcrumbs(
                $request->route()->getName(),
                $request->route()->originalParameters()
            ),
            "pageHead" => [
                "title"   => $user->username,
                "actions" => [
                    [
                        "type"  => "button",
                        "style" => "exitEdit",
                        "route" => [
                            "name"       => preg_replace('/edit$/', "show", $request->route()->getName()),
                            "parameters" => array_values($request->route()->originalParameters()),
                        ],
                    ],
                ],
            ],

            "formData" => [
                "blueprint" => [
                    [
                        "label"   => __("Profile Information"),
                        "title"   => __("id"),
                        "icon"    => "fa-light fa-user",
                        "current" => true,
                        "fields"  => [
                            "username" => [
                                "type"        => "input",
                                "label"       => __("username"),
                                "placeholder" => __("johndoe"),
                                "value"       => $user->username ?? '',
                            ],
                            "email" => [
                                "type"        => "input",
                                "label"       => __("email"),
                                "placeholder" => __("example@mail.com"),
                                "value"       => $user->email ?? '',
                            ],
                            "password" => [
                                "type"        => "password",
                                "label"       => __("password"),
                                "placeholder" => __("********"),
                                "value"       => '',
                            ],
                        ],
                    ],
                    "permissions" => [
                        "label"   => __("Permissions"),
                        "title"   => __("Permissions"),
                        "icon"    => "fa-light fa-user-lock",
                        "current" => false,
                        "fields"  => [
                            "permissions" => [
                                "full"              => true,
                                "type"              => "permissions",
                                "label"             => __("permissions"),
                                "value"             => $permissions,
                                "fullComponentArea" => true,
                            ],
                        ],
                    ],
                ],
                "args" => [
                    "updateRoute" => [
                        "name"       => "grp.models.user.update",
                        "parameters" => [$user->id],
                    ],
                ],
            ],
        ]);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowUser::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', "show", $routeName),
            routeParameters: $routeParameters,
            suffix: "(" . __("editing") . ")"
        );
    }
}
