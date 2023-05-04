<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 May 2023 07:49:27 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


/** @noinspection PhpUnused */

namespace App\Actions\Tenancy\Group;

use App\Actions\WithStorageLink;
use App\Actions\WithTenantsArgument;
use App\Models\Tenancy\Group;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateGroupStorageLink
{
    use AsAction;
    use WithTenantsArgument;
    use WithStorageLink;

    public string $commandSignature   = 'create:group-storage-link {group} ';
    public string $commandDescription = 'Create the group storage link for the given group(s).';


    public function handle(Group $group): array
    {

        return  $this->setStorageLink(
            'groups',
            $group->ulid.'-'.$group->slug
        );


    }

    public function asCommand(Command $command): int
    {

        try {
            $group =Group::where('slug', $command->argument('group'))->firstOrFail();
        } catch (Exception) {
            $command->error("The group [".$command->argument('group')."] does not exist.");
            return 1;
        }

        $this->handle($group);




        return 0;
    }
}
