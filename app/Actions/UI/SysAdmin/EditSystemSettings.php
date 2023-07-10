<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Jun 2023 14:48:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\SysAdmin;

use App\Actions\UI\Dashboard\Dashboard;
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
                            "title"  => __("google api key"),
                            "icon"   => "fab fa-google",
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
                Dashboard::make()->getBreadcrumbs(),
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
