<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\Assets\Language\UI\GetLanguagesOptions;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\UI\LoggedUserResource;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class EditProfile
{
    use AsAction;
    use WithInertia;

    public function asController(ActionRequest $request): User
    {
        return $request->user();
    }

    public function jsonResponse(User $user)
    {
        return $this->generateBlueprint($user);
    }

    public function generateBlueprint(User $user)
    {
        return [
            "title"       => __("Edit Profile"),
            "breadcrumbs" => $this->getBreadcrumbs(),
            "pageHead"    => [
                "title"        => __("Edit Profile"),
                'actions'      => [
                    [
                        'type'  => 'button',
                        'style' => 'exit',
                        'label' => __('back to profile'),
                        'route' => [
                            'name'       => 'grp.profile.show',
                            'parameters' => array_values(request()->route()->originalParameters())
                        ],
                    ]
                ]
            ],
            "formData" => [
                "blueprint" => [
                    [
                        "label"   => __("profile"),
                        "icon"    => "fa-light fa-user-circle",
                        "current" => true,
                        "fields"  => [
                            "email"  => [
                                "type"  => "input",
                                "label" => __("email"),
                                "value" => $user->email,
                            ],
                            "password" => [
                                "type"  => "password",
                                "label" => __("password"),
                                "value" => "",
                            ],
                            "about"  => [
                                "type"          => "textarea",
                                "label"         => __("about"),
                                "value"         => $user->about,
                                "maxLength"     => 48,
                                "counter"       => true,
                                "rows"          => 5,
                                "placeholder"   => __('Enter up to 50 characters')
                            ],
                            "avatar" => [
                                "type"  => "avatar",
                                "label" => __("photo"),
                                "value" => $user->avatarImageSources(320, 320)
                            ],
                        ],
                    ],
                    // [
                    //     "label"  => __("password"),
                    //     "icon"   => "fa-light fa-key",
                    //     "fields" => [
                    //     ],
                    // ],
                    [
                        "label"  => __("Settings"),
                        "icon"   => "fal fa-cog",
                        "fields" => [
                            "language_id" => [
                                "type"    => "select",
                                "label"   => __("language"),
                                "value"   => $user->language_id,
                                'options' => GetLanguagesOptions::make()->translated(),
                            ],
                            "app_theme" => [
                                "type"  => "app_theme",
                                "label" => __("theme color"),
                                "value" => Arr::get($user->settings, 'app_theme'),
                            ],
                        ],
                    ],
                    // [
                    //     "label"  => __("appearance"),
                    //     "icon"   => "fa-light fa-paint-brush",
                    //     "fields" => [
                    //         "colorMode" => [
                    //             "type"  => "colorMode",
                    //             "label" => __("turn dark mode"),
                    //             "value" => "",
                    //         ],
                    //         "theme"     => [
                    //             "type"  => "theme",
                    //             "label" => __("choose your theme"),
                    //             "value" => "",
                    //         ],
                    //     ],
                    // ],
                    // [
                    //     "label"  => __("notifications"),
                    //     "icon"   => "fa-light fa-bell",
                    //     "fields" => [
                    //         "notifications" => [
                    //             "type"  => "myNotifications",
                    //             "label" => __("notifications"),
                    //             "value" => [],
                    //             "data"  => [
                    //                 [
                    //                     'type' => 'new-order',
                    //                     'label'=> __('new order'),
                    //                 ],
                    //                 [
                    //                     'type' => 'new re',
                    //                     'label'=> __('new order'),
                    //                 ],
                    //                 [
                    //                     'type' => 'new user',
                    //                     'label'=> __('new order'),
                    //                 ],
                    //             ]
                    //         ],

                    //     ],
                    // ],
                    [
                        'label'  => __('App'),
                        'icon'   => 'fal fa-mobile-android-alt',
                        'fields' => [
                            "app_login" => [
                                "type"          => "app_login",
                                "label"         => __("App login"),
                                "route"         => [
                                    "name"  => "grp.models.profile.app-login-qrcode",
                                ],
                                "noSaveButton"  => true,
                                "noTitle"       => true,
                                "full"          => true
                            ],
                        ]
                    ]
                ],
                "args"      => [
                    "updateRoute" => [
                        "name"       => "grp.models.profile.update"
                    ],
                ],
            ],
            'auth'          => [
                'user' => LoggedUserResource::make($user)->getArray(),
            ],
        ];
    }

    public function htmlResponse(User $user): Response
    {

        return Inertia::render("EditModel", $this->generateBlueprint($user));
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(ShowDashboard::make()->getBreadcrumbs(), [
            [
                "type"   => "simple",
                "simple" => [
                    "route" => [
                        "name" => "grp.profile.show",
                    ],
                    "label" => __("my profile"),
                ],
            ],
        ]);
    }
}
