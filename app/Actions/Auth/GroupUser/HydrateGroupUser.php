<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 30 Apr 2023 20:26:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser;

use App\Actions\Auth\GroupUser\Hydrators\GroupUserHydrateTenants;
use App\Models\Auth\GroupUser;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class HydrateGroupUser
{
    use AsAction;

    public string $commandSignature = 'hydrate:central-user {--username}';


    public function handle(GroupUser $centralUser): void
    {
        GroupUserHydrateTenants::run($centralUser);
    }


    protected function getModel($username): ?GroupUser
    {
        return GroupUser::where('username', $username)->first();
    }
    protected function getAllModels(): Collection
    {
        return GroupUser::all();
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
