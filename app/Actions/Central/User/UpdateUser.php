<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:03:02 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\User;

use App\Actions\Central\User\Hydrators\UserHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Central\User;

class UpdateUser
{
    use WithActionUpdate;

    public function handle(User $user, array $modelData): User
    {
        $user = $this->update($user, $modelData, ['data']);
        UserHydrateUniversalSearch::dispatch($user);
        return $user;
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }
}
