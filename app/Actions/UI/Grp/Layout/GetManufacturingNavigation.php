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

        if ($user->hasPermissionTo("manufacturing.$production->id.view")) {
            $navigation["warehouse"] = [
                "root"  => "grp.org.productions.show.infrastructure.",
                "label" => __("locations"),
                "icon"  => ["fal", "fa-industry"],
                "route" => [
                    "name"       => "grp.org.productions.show.infrastructure.dashboard",
                    "parameters" => [$production->organisation->slug, $production->slug],
                ],
                "topMenu" => [
                    "subSections" => [

                    ],
                ],
            ];
        }

        return $navigation;
    }
}
