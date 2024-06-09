<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 20:34:57 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\SysAdmin;

use App\Actions\Helpers\GoogleDrive\Traits\WithTokenPath;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class EditSystemSettings
{
    use AsAction;
    use WithInertia;
    use WithTokenPath;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("sysadmin.edit");
    }


    public function asController()
    {

    }


    public function htmlResponse(): Response
    {

        $organisation= app('currentTenant');
        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('system settings'),
                'pageHead'    => [
                    'title' => __('system settings'),
                ],
                "formData" => [
                    "blueprint" => [

                        [
                            "title"  => __("branding"),
                            "icon"   => "fa-light fa-copyright",
                            "fields" => [
                                "name" => [
                                    "type"  => "input",
                                    "label" => __("Name"),
                                    "value" => Arr::get($organisation->settings, 'ui.name', $organisation->name)
                                ],
                                "logo" => [
                                    "type"  => "avatar",
                                    "label" => __("logo"),
                                    "value" => $organisation->logo_id,
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
                                "theme"     => [
                                    "type"  => "theme",
                                    "label" => __("choose your theme"),
                                    "value" => "",
                                ],
                            ],
                        ],
                        [
                            "title"  => __("google drive"),
                            "icon"   => "fab fa-google",
                            "button" => [
                                "title"   => !file_exists($this->getTokenPath()) ? "Authorize" : "Authorized",
                                "route"   => route('grp.google.drive.authorize'),
                                "disable" => file_exists($this->getTokenPath())
                            ],
                            "fields" => [
                                "google_client_id" => [
                                    "type"  => "input",
                                    "label" => __("client ID"),
                                    "value" => Arr::get($organisation->settings, 'google.id')
                                ],
                                "google_client_secret" => [
                                    "type"  => "input",
                                    "label" => __("client secret"),
                                    "value" => Arr::get($organisation->settings, 'google.secret')
                                ],
                                "google_drive_folder_key" => [
                                    "type"  => "input",
                                    "label" => __("google drive folder key"),
                                    "value" => Arr::get($organisation->settings, 'google.drive.folder')
                                ],
                                "google_redirect_uri" => [
                                    "type"       => "input",
                                    "label"      => __("google redirect URI"),
                                    "value"      => url('/'),
                                    "readonly"   => true,
                                    "copyButton" => true,
                                ]
                            ],
                        ],
                    ],
                    "args"      => [
                        "updateRoute" => [
                            "name"       => "models.system-settings.update"
                        ],
                    ],
                ],


            ]
        );
    }



    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.sysadmin.settings.edit'
                            ],
                            'label'  => __('settings'),
                        ]
                    ]
                ]
            );
    }
}
