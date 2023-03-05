<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 19:02:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\CentralUser\Hydrators;

use App\Models\Central\CentralUser;
use App\Models\SysAdmin\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class CentralUserHydrateTenants implements ShouldBeUnique
{
    use AsAction;

    public function handle(CentralUser $centralUser): void
    {
        $userNameInfo = [];

        foreach ($centralUser->tenants as $tenant) {
            $userName       = $tenant->execute(function () use ($centralUser) {
                $user = User::withTrashed()->where('central_user_id', $centralUser->id)->first();


                if ($user && $user->parentWithTrashed) {
                    return $user->parentWithTrashed->name;
                }


                return '';
            });
            $userNameInfo[] = array('tenant' => $tenant->code, 'name' => $userName);
        }

        $userNameInfo = collect($userNameInfo)->groupBy('name')->all();
        foreach ($userNameInfo as $key => $value) {
            $userNameInfo[$key] = $value->pluck('tenant')->all();
        }



        $centralUser->update(
            [
                'number_tenants' => $centralUser->tenants()->count(),
                'data->names'    => $userNameInfo
            ]
        );
    }

    public function getJobUniqueId(CentralUser $centralUser): string
    {
        return $centralUser->id;
    }
}
