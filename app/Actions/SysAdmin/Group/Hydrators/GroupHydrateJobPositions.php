<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:14:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Models\SysAdmin\Group;
use App\Models\HumanResources\JobPosition;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateJobPositions
{
    use AsAction;

    public function handle(Group $group): void
    {
        $stats = [
            'number_job_positions' => JobPosition::count()
        ];
        $group->humanResourcesStats()->update($stats);
    }
}
