<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Auth\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateAuth implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(User $user): void
    {
        $stats        = [];
        $numberLogins = $user->stats->number_logins;

        if(auth()->check()) {

            $stats = [
                'login_at'      => now(),
                'last_login'    => request()->ip(),
                'number_logins' => $numberLogins + 1
            ];
        }

        if(! auth()->check()) {
            $stats = [
                'failed_login' => request()->ip(),
                'failed_login_at' => now()
            ];
        }

        $user->stats()->update($stats);
    }

    public function getJobUniqueId(User $user): string
    {
        return $user->id;
    }
}
