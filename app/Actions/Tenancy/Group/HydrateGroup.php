<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Group;

use App\Actions\HydrateModel;
use App\Actions\Tenancy\Group\Hydrators\GroupHydrateTenants;
use App\Actions\Traits\WithNormalise;
use App\Models\Tenancy\Group;
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
