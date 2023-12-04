<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\Hydrators;

use App\Models\SysAdmin\User;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateFailLogin
{
    use AsAction;

    public function handle(User $user, string $ip, Carbon $datetime): void
    {

        $stats = [
            'number_failed_logins'    => $user->stats->number_failed_logins+1,
            'last_failed_login_ip'    => $ip,
            'last_failed_login_at'    => $datetime
        ];

        $user->stats()->update($stats);
    }


}
