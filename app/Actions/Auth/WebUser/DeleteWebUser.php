<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 12:56:23 Malaysia Time, Sanur, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\WebUser;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithOrganisationArgument;
use App\Models\Auth\WebUser;
use Illuminate\Console\Command;

class DeleteWebUser
{
    use WithActionUpdate;
    use WithOrganisationArgument;

    public string $commandSignature = 'delete:web-user {tenant} {id}';

    public function handle(WebUser $webUser, array $deletedData=[], bool $skipHydrate = false): WebUser
    {
        $webUser->delete();
        $webUser=$this->update($webUser, $deletedData, ['data']);

        if (!$skipHydrate) {
            CustomerHydrateWebUsers::dispatch($webUser->customer);
        }
        return $webUser;
    }

    public function asCommand(Command $command): int
    {
        $this->getTenant($command)->execute(
            fn () => $this->handle(WebUser::findOrFail($command->argument('id')))
        );

        return 0;
    }
}
