<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

 namespace App\Actions\UI\Grp;

use App\Actions\GrpAction;
use App\Actions\InertiaAction;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditGroup extends GrpAction
{
    public function handle(): Group
    {
        $group = group();

        return $group;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("sysadmin.view");
    }

    public function asController(ActionRequest $request): Group
    {
        $this->initialisation(group(), $request);
        return $this->handle(group());
    }


    public function htmlResponse(Group $group, ActionRequest $request): Response
    {

        $group = group();
        return Inertia::render("EditModel", [
            "title"       => __("group"),
            "breadcrumbs" => $this->getBreadcrumbs(
                $request->route()->getName(),
                $request->route()->originalParameters()
            ),
            "pageHead" => [
                "title"   => $group->name,
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
                        "label"   => __("Group Information"),
                        "title"   => __("id"),
                        "icon"    => "fa-light fa-user",
                        "current" => true,
                        "fields"  => [
                            "name" => [
                                "type"        => "input",
                                "label"       => __("name"),
                                "value"       => $group->name ?? '',
                            ],
                            "email" => [
                                "type"        => "input",
                                "label"       => __("email"),
                                "value"       => $group->email ?? '',
                            ],
                            // "avatar" => [
                            //     "type"  => "avatar",
                            //     "label" => __("photo"),
                            //     "value" => $group->avatarImageSources(320, 320)
                            // ],
                        ],
                    ],
                ],
                "args" => [
                    "updateRoute" => [
                        "name"       => "grp.models.user.update",
                        "parameters" => [$group->id],
                    ],
                ],
            ],
        ]);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowGroup::make()->getBreadcrumbs(
            routeParameters: $routeParameters,
            suffix: "(" . __("editing") . ")"
        );
    }
}
