<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 28 Sept 2024 00:01:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\SysAdmin\User\Hydrators\UserHydrateAuthorisedModels;
use App\Actions\SysAdmin\User\Hydrators\UserHydrateModels;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachEmployeeToUser
{
    use AsAction;

    public function handle(User $user, Employee $employee): User
    {

        $user->employees()->syncWithoutDetaching([$employee->id]);
        UserHydrateAuthorisedModels::run($user);
        UserHydrateModels::dispatch($user);

        return $user;
    }

}
