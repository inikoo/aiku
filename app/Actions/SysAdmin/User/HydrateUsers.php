<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 29 Sept 2024 17:14:21 Malaysia Time, Cyberjaya, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\HydrateModel;
use App\Actions\SysAdmin\User\Hydrators\UserHydrateAuthorisedModels;
use App\Actions\SysAdmin\User\Hydrators\UserHydrateModels;
use App\Models\SysAdmin\User;
use Illuminate\Support\Collection;

class HydrateUsers extends HydrateModel
{
    public string $commandSignature = 'hydrate:users {organisations?*} {--s|slugs=}';


    public function handle(User $user): void
    {
        UserHydrateAuthorisedModels::run($user);
        UserHydrateModels::run($user);
    }

    protected function getModel(string $slug): User
    {
        return User::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return User::withTrashed()->get();
    }
}
