<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Feb 2023 22:05:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebUser;

use App\Actions\Sales\Customer\HydrateCustomer;
use App\Actions\WithActionUpdate;
use App\Models\Web\WebUser;
use Illuminate\Console\Command;


class DeleteWebUser
{
    use WithActionUpdate;

    public string $commandSignature = 'delete:web-user {tenant} {id}';

    public function handle(WebUser $webUser, array $deletedData=[], bool $skipHydrate = false): WebUser
    {
         $webUser->delete();
        $webUser=$this->update($webUser,$deletedData,['data']);

        if (!$skipHydrate) {
            HydrateCustomer::make()->webUsers($webUser->customer);
        }

        return $webUser;
    }

    /**
     * @throws \Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById
     */
    public function asCommand(Command $command): int
    {
        $tenant = tenancy()->query()->where('code', $command->argument('tenant'))->first();
        tenancy()->initialize($tenant);

        $this->handle(WebUser::findOrFail($command->argument('id')));

        return 0;
    }

}
