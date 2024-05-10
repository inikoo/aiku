<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 18:59:34 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Layout;

use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetManufacturingNavigation
{
    use AsAction;

    public function handle(Production $production, User $user): array
    {
        $navigation = [];

        // if ($user->hasPermissionTo("manufacturing.$production->id.view")) {
        $navigation["raw_materials"] = [
            "root"  => "grp.org.productions.show.infrastructure.",
            "label" => __("Raw Materials"),
            "icon"  => "fal fa-drone",
            // "route" => [
            //     "name"       => "grp.org.productions.show.infrastructure.dashboard",
            //     "parameters" => [$production->organisation->slug, $production->slug],
            // ],
            // "topMenu" => [
            //     "subSections" => [

            //     ],
            // ],
        ];
        $navigation["job_orders"] = [
            "root"  => "grp.org.productions.show.infrastructure.",
            "label" => __("Job Orders"),
            "icon"  => "fal fa-sort-shapes-up",
            // "route" => [
            //     "name"       => "grp.org.productions.show.infrastructure.dashboard",
            //     "parameters" => [$production->organisation->slug, $production->slug],
            // ],

        ];
        $navigation["artifacts"] = [
            "root"  => "grp.org.productions.show.infrastructure.",
            "label" => __("artifacts"),
            "icon"  => "fal fa-window-frame-open",
            // "route" => [
            //     "name"       => "grp.org.productions.show.infrastructure.dashboard",
            //     "parameters" => [$production->organisation->slug, $production->slug],
            // ],

        ];
        $navigation["manufacture_tasks"] = [
            "root"  => "grp.org.productions.show.infrastructure.",
            "label" => __("Manufacture Tasks"),
            "icon"  => "fal fa-tasks",
            // "route" => [
            //     "name"       => "grp.org.productions.show.infrastructure.dashboard",
            //     "parameters" => [$production->organisation->slug, $production->slug],
            // ],

        ];
        // }

        return $navigation;
    }
}
