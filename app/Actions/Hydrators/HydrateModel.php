<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Jan 2022 21:09:21 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Hydrators;

use App\Actions\WithTenantsArgument;
use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;


class HydrateModel
{
    use AsAction;
    use WithTenantsArgument;

    protected Tenant $tenant;


    protected function getModel(int $id): ?Model
    {
        return null;
    }

    protected function getAllModels(): Collection
    {
        return new Collection();
    }


    public function asCommand(Command $command): int
    {
        $tenants  = $this->getTenants($command);
        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->run(function () use ($command) {
                if ($command->argument('id')) {
                    if ($model = $this->getModel($command->argument('id'))) {
                        $this->handle($model);
                        $command->info('Done!');
                    }
                } else {
                    $this->loopAll($command);
                }
            });

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }


    protected function loopAll(Command $command): void
    {
        $command->withProgressBar($this->getAllModels(), function ($model) {
            if ($model) {
                $this->handle($model);
            }
        });
        $command->info("");
    }

}


