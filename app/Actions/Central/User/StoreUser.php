<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 06 Nov 2022 13:40:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\User;

use App\Actions\Central\User\Hydrators\UserHydrateUniversalSearch;
use App\Models\Central\User;
use App\Models\Tenancy\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreUser
{
    use AsAction;

    public function handle(Tenant $tenant, $modelData): User
    {
        /** @var User $user */
        $user = $tenant->users()->create($modelData);
        UserHydrateUniversalSearch::dispatch($user);
        return $user;
    }
}
