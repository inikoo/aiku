<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:04:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;


use App\Models\Central\CentralUser;
use App\Models\Central\Tenant;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreUser
{
    use AsAction;

    public function handle(Tenant $tenant, Guest|Employee $parent, CentralUser $centralUser): User
    {
        $modelData['password'] = Hash::make($modelData['password']);


        $user=User::create($modelData);




        return $this->finalise($user);
    }


}
