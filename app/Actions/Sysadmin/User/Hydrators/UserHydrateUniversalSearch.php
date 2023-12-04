<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 17:45:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sysadmin\User\Hydrators;

use App\Actions\Traits\WithRoutes;

use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateUniversalSearch
{
    use AsAction;

    use WithRoutes;

    public function handle(User $user): void
    {
        $user->universalSearch()->updateOrCreate(
            [],
            [
                'section'        => 'sysadmin',
                'title'          => $user->username,
                'description'    => trim($user->email.' '.$user->contact_name)
            ]
        );
    }


}
