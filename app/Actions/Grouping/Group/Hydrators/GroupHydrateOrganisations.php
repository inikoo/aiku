<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Grouping\Group\Hydrators;

use App\Models\Grouping\Group;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateOrganisations implements ShouldBeUnique
{
    use AsAction;

    public function handle(Group $group): void
    {

        $group->update(
            [
                'number_organisations' => $group->organisations()->count()
            ]
        );
    }

    public function getJobUniqueId(Group $group): int
    {
        return $group->id;
    }



}
