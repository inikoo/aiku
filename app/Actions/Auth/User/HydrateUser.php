<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:17 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\Auth\User\Hydrators\UserHydrateTenants;
use App\Actions\HydrateModel;
use App\Models\Auth\User;
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
