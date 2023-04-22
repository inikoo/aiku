<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Group;

use App\Actions\Central\Group\Hydrators\GroupHydrateTenants;
use App\Actions\HydrateModel;
use App\Actions\Traits\WithNormalise;
use App\Models\Central\Group;
use Exception;
use Illuminate\Console\Command;

class HydrateGroup extends HydrateModel
{
    use WithNormalise;

    public string $commandSignature = 'hydrate:group {group_slug}';


    public function handle(Group $group): void
    {
        GroupHydrateTenants::run($group);
    }




    public function asCommand(Command $command): int
    {
        try {
            $group = Group::where('slug', $command->argument('group_slug'))->firstorFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        $this->handle($group);

        return 0;
    }
}
