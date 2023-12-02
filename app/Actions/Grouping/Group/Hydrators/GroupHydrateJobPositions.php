<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Nov 2023 20:42:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Grouping\Group\Hydrators;

use App\Models\HumanResources\JobPosition;
use App\Models\Grouping\Group;
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
