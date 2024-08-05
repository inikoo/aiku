<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Jul 2024 23:57:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Actions\Helpers\GoogleDrive\Traits\WithTokenPath;
use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditOrganisationSettings extends OrgAction
{
    use WithTokenPath;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);
    }


    public function asController(Organisation $organisation, ActionRequest $request): Organisation
    {
        $this->initialisation($organisation, $request);

        return $organisation;
    }



    public function htmlResponse(Organisation $organisation): Response
    {

        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('Organisation settings'),
                'pageHead'    => [
                    'title' => __('Organisation settings'),
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
                                    "value" => $organisation->imageSources(320, 320),
                                ],
                            ],
                        ],
                        [
                            "title"  => __("appearance"),
                            "icon"   => "fa-light fa-paint-brush",
                            "fields" => [

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
                                "route"   => [
                                    'name'       => 'grp.models.org.google_drive.authorize',
                                    'parameters' => [$organisation->id]
                                ],
                                "disable" => file_exists($this->getTokenPath())
                            ],

                            "fields" => [
                                "google_client_id" => [
                                    "type"  => "password",
                                    "label" => __("client ID"),
                                    "value" => Arr::get($organisation->settings, 'google.id')
                                ],
                                "google_client_secret" => [
                                    "type"  => "password",
                                    "label" => __("client secret"),
                                    "value" => Arr::get($organisation->settings, 'google.secret')
                                ],
                                "google_drive_folder_key" => [
                                    "type"  => "password",
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
                            "name"       => "grp.models.org.settings.update",
                            "parameters" => [$organisation->id],
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
                                'name'       => 'grp.org.settings.edit',
                                'parameters' => [$this->organisation->slug]
                            ],
                            'label'  => __('Organisation settings'),
                        ]
                    ]
                ]
            );
    }
}
