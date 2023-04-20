<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 06 Oct 2022 14:11:53 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

/** @noinspection PhpUnused */

namespace App\Actions\Central\Tenant;

use App\Actions\WithTenantsArgument;
use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTenantStorageLink
{
    use AsAction;
    use WithTenantsArgument;

    public string $commandSignature   = 'create:tenant-storage-link {tenants?*} ';
    public string $commandDescription = 'Create the symbolic links configured for the application';


    public function handle(): array
    {
        /** @var Tenant $tenant */
        $tenant   = app('currentTenant');
        $linkBase = public_path('tenants');
        $link     = $linkBase.'/'.$tenant->ulid.'-'.$tenant->code;
        $target   = storage_path('app/public');

        if (!file_exists($linkBase)) {
            mkdir($linkBase, 0777, true);
        }
        if (!file_exists($target)) {
            mkdir($target, 0777, true);
        }

        if (file_exists($link)) {
            if (!is_link($link)) {
                return array('success' => false, 'target' => $target, 'link' => $link);
            }
            unlink($link);
        }
        symlink($target, $link);

        return array('success' => true, 'target' => $target, 'link' => $link);
    }

    public function asCommand(Command $command): int
    {
        $tenants  = $this->getTenants($command);
        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->execute(
                function () use ($command, $tenant) {
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
