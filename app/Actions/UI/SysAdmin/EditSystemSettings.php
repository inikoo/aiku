<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Jun 2023 14:48:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\SysAdmin;

use App\Actions\Google\Drive\Traits\WithTokenPath;
use App\Actions\UI\Dashboard\ShowDashboard;
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

        $tenant= app('currentTenant');
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
                                    "value" => Arr::get($tenant->settings, 'ui.name', $tenant->name)
                                ],
                                "logo" => [
                                    "type"  => "avatar",
                                    "label" => __("logo"),
                                    "value" => $tenant->logo_id,
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
                                "title" => !file_exists($this->getTokenPath()) ? "Authorize" : "Authorized",
                                "route" => route('google.drive.authorize'),
                                "disable" => file_exists($this->getTokenPath())
                            ],
                            "fields" => [
                                "google_client_id" => [
                                    "type"  => "input",
                                    "label" => __("client ID"),
                                    "value" => Arr::get($tenant->settings, 'google.id')
                                ],
                                "google_client_secret" => [
                                    "type"  => "input",
                                    "label" => __("client secret"),
                                    "value" => Arr::get($tenant->settings, 'google.secret')
                                ],
                                "google_drive_folder_key" => [
                                    "type"  => "input",
                                    "label" => __("google drive folder key"),
                                    "value" => Arr::get($tenant->settings, 'google.drive.folder')
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
                                'name' => 'sysadmin.settings.edit'
                            ],
                            'label'  => __('settings'),
                        ]
                    ]
                ]
            );
    }
}
