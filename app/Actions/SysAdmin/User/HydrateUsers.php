<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 29 Sept 2024 17:14:21 Malaysia Time, Cyberjaya, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\HydrateModel;
use App\Actions\SysAdmin\User\Hydrators\UserHydrateModels;
use App\Models\SysAdmin\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class HydrateUsers extends HydrateModel
{
    public string $commandSignature = 'hydrate:users';


    public function handle(User $user): void
    {
        SetUserAuthorisedModels::run($user);
        UserHydrateModels::run($user);
    }

    public function asCommand(Command $command): int
    {
        $count = User::count();
        $bar   = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();
        User::chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });
        $bar->finish();


        return 0;
    }
}
