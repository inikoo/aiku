<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

/** @noinspection PhpUnused */

namespace App\Actions\Grouping\Organisation;

use App\Actions\Traits\WithStorageLink;
use App\Actions\Traits\WithOrganisationsArgument;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTenantStorageLink
{
    use AsAction;
    use WithOrganisationsArgument;
    use WithStorageLink;

    public string $commandSignature   = 'create:tenant-storage-link {tenants?*} ';
    public string $commandDescription = 'Create the symbolic links configured for the application';


    public function handle(): array
    {
        /** @var \App\Models\Grouping\Organisation $organisation */
        $organisation   = app('currentTenant');

        return  $this->setStorageLink(
            'tenants',
            $organisation->ulid.'-'.$organisation->slug
        );

    }

    public function asCommand(Command $command): int
    {
        $organisations  = $this->getTenants($command);
        $exitCode       = 0;

        foreach ($organisations as $organisation) {
            $result = (int)$organisation->execute(
                function () use ($command, $organisation) {
                    $result = $this->handle();
                    if ($result['success']) {
                        $command->info("The [".$result['link']."] link has been connected to[".$result['target']."] .");

                        return 0;
                    }
                    $command->error("The [".$result['link']."] name has been taken by a normal file.");

                    return 1;
                }
            );
            if ($result !== 0) {
                $exitCode = $result;
            }
        }


        return $exitCode;
    }
}
