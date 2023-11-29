<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Group;

use App\Actions\HydrateModel;
use App\Actions\Organisation\Group\Hydrators\GroupHydrateInventory;
use App\Actions\Organisation\Group\Hydrators\GroupHydrateProcurement;
use App\Actions\Organisation\Group\Hydrators\GroupHydrateOrganisations;
use App\Actions\Traits\WithNormalise;
use App\Models\Organisation\Group;
use Exception;
use Illuminate\Console\Command;

class HydrateGroup extends HydrateModel
{
    use WithNormalise;

    public string $commandSignature = 'hydrate:group {group_slug}';


    public function handle(Group $group): void
    {
        GroupHydrateOrganisations::run($group);
        GroupHydrateProcurement::run($group);
        GroupHydrateInventory::run($group);
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
