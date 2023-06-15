<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Central\User\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Central\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(User $user): void
    {
        $user->universalSearch()->create(
            [
                'primary_term'   => $user->username,
                'secondary_term' => $user->email
            ]
        );
    }


}
