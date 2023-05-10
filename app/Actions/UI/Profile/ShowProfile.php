<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Auth\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowProfile
{
    use AsAction;
    use WithInertia;

    public function asController(ActionRequest $request): User
    {
        return $request->user();
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function htmlResponse(User $user): Response
    {
        $this->validateAttributes();

        return Inertia::render("EditModel", [
            "title"       => __("Profile"),
            "breadcrumbs" => $this->getBreadcrumbs(),
            "pageHead"    => [
                "title" => __("My Profile"),
            ],
            "profile"  => $user->only("username", "email", "avatar", "about"),
            "pageBody" => [
                "current" => "profile",
                "layout"  => [
                    "profile" => [
                        "title" => __("Profile"),
                        "notes" => __(
                            "This information will be synchronised in all your workspaces."
                        ),
                        "icon"    => "fa-light fa-user-circle",
                        "current" => true,
                        "fields"  => [
                            "username" => [
                                "label" => __("Username"),
                            ],
                            "about" => [
                                "label" => __("About"),
                                "notes" => __("Brief description for your profile."),
                            ],
                            "avatar" => [
                                "label" => __("avatar"),
                                "info"  => __("user photo or icon"),
                            ],
                        ],
                    ],
                    "password" => [
                        "title"  => __("Password"),
                        "icon"   => "fa-light fa-key",
                        "fields" => [
                            "password" => [
                                "label" => __("Password"),
                            ],
                        ],
                    ],
                    "workplaces" => [
                        "title" => __("Workplaces"),
                        "icon"  => "fa-light fa-clone",
                    ],
                    "appearance" => [
                        "title"  => __("Appearance"),
                        "icon"   => "fa-light fa-paint-brush",
                        "fields" => [
                            "colormode" => [
                                "label" => __("Turn Dark Mode"),
                            ],
                            "theme" => [
                                "label" => __("Select Theme"),
                            ],
                        ],
                    ],
                ],
            ],

            "formData" => [
                "blueprint" => [
                    [
                        "title" => __("profile"),
                        "icon"  => "fa-light fa-user-circle",
                        "notes" => __(
                            "This information will be synchronised in all your workspaces."
                        ),
                        "current" => true,
                        "fields"  => [
                            "email" => [
                                "type"  => "input",
                                "label" => __("email"),
                                "value" => $user->email,
                            ],
                            "about" => [
                                "type"  => "textarea",
                                "label" => __("about"),
                                "value" => $user->about,
                            ],
                            "avatar" => [
                                "type"  => "avatar",
                                "label" => __("photo"),
                                "value" => $user->photo,
                            ],
                        ],
                    ],
                    [
                        "title"  => __("password"),
                        "icon"   => "fa-light fa-key",
                        "fields" => [
                            "password" => [
                                "type"  => "password",
                                "label" => __("password"),
                                "value" => "",
                            ],
                        ],
                    ],
                    [
                        "title"  => __("appearance"),
                        "icon"   => "fa-light fa-paint-brush",
                        "fields" => [
                            "colorMode" => [
                                "type"  => "colorMode",
                                "label" => __("turn dark mode"),
                                "value" => "",
                            ],
                            "theme" => [
                                "type"  => "theme",
                                "label" => __("choose your theme"),
                                "value" => "",
                            ],
                        ],
                    ],
                ],
                "args" => [
                    "updateRoute" => [
                        "name"       => "models.user.update",
                        "parameters" => $user->username,
                    ],
                ],
            ],
        ]);
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(Dashboard::make()->getBreadcrumbs(), [
            [
                "type"   => "simple",
                "simple" => [
                    "route" => [
                        "name" => "profile.show",
                    ],
                    "label" => __("my profile"),
                ],
            ],
        ]);
    }
}
