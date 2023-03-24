<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Feb 2023 22:05:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebUser;

use App\Actions\Sales\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Actions\WithActionUpdate;
use App\Actions\WithTenantArgument;
use App\Models\Web\WebUser;
use Illuminate\Console\Command;

class DeleteWebUser
{
    use WithActionUpdate;
    use WithTenantArgument;

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
