<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:13:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPermissionGroupData
{
    use AsAction;

    public function handle(Employee|User|Guest $user, Group $group): array
    {
        return (array) $user->permissions->map(function ($permission) use ($group) {
            return [$permission->name];
        })->reduce(function ($carry, $item) {
            return array_merge_recursive($carry, $item);
        }, []);
    }
}
