<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 19:02:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\CentralUser;

use App\Actions\Central\CentralUser\Hydrators\CentralUserHydrateTenants;
use App\Models\Central\CentralUser;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;


class HydrateCentralUser
{

    use AsAction;

    public string $commandSignature = 'hydrate:central-user {--username}';


    public function handle(CentralUser $centralUser): void
    {

        CentralUserHydrateTenants::run($centralUser);
    }


    protected function getModel($username): ?CentralUser
    {
        return CentralUser::where('username',$username)->first();
    }
    protected function getAllModels(): Collection
    {
        return CentralUser::all();
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

    public function asCommand(Command $command): int
    {

        if ($command->option('username')) {
            if ($model = $this->getModel($command->option('username'))) {
                $this->handle($model);
                $command->info('Done!');
            }
        } else {
            $this->loopAll($command);
        }

        return 0;

    }


}


