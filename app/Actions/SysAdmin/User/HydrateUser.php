<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:37:54 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\HydrateModel;
use App\Actions\SysAdmin\User\Hydrators\UserHydrateTenants;
use App\Models\SysAdmin\User;
use Illuminate\Support\Collection;

class HydrateUser extends HydrateModel
{
    public string $commandSignature = 'hydrate:users {tenants?*} {--i|id=}';


    public function handle(User $user): void
    {
        UserHydrateTenants::run($user);
    }

    protected function getModel(int $id): User
    {
        return User::findOrFail($id);
    }

    protected function getAllModels(): Collection
    {
        return User::all();
    }
}
