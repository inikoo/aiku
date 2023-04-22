<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 22 Apr 2023 15:02:02 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Group\Hydrators;

use App\Models\Central\Group;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateTenants implements ShouldBeUnique
{
    use AsAction;

    public function handle(Group $group): void
    {

        $group->update(
            [
                'number_tenants' => $group->tenants()->count()
            ]
        );
    }

    public function getJobUniqueId(Group $group): int
    {
        return $group->id;
    }



}
