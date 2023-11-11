<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 May 2023 07:49:27 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


/** @noinspection PhpUnused */

namespace App\Actions\Organisation\Group;

use App\Actions\Traits\WithStorageLink;
use App\Actions\Traits\WithOrganisationsArgument;
use App\Models\Organisation\Group;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateGroupStorageLink
{
    use AsAction;
    use WithOrganisationsArgument;
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
