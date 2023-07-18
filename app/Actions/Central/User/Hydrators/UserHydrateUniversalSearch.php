<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Central\User\Hydrators;

use App\Actions\Traits\WithRoutes;
use App\Actions\Traits\WithTenantJob;
use App\Models\Auth\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;
    use WithRoutes;

    public function handle(User $user): void
    {
        $user->universalSearch()->create(
            [
                'section'        => 'sysadmin',
                'title'          => $user->username,
                'description'    => trim($user->email.' '.$user->contact_name)
            ]
        );
    }


}
