<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 30 Apr 2023 20:26:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser\Hydrators;

use App\Models\Auth\GroupUser;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupUserHydrateTenants implements ShouldBeUnique
{
    use AsAction;

    public function handle(GroupUser $groupUser): void
    {

        $userNameInfo = [];

        foreach ($groupUser->tenants as $organisation) {
            $userNameInfo[] = array('tenant' => $organisation->slug, 'name' =>Arr::get($organisation->pivot->data, 'name', 'error'));
        }

        $userNameInfo = collect($userNameInfo)->groupBy('name')->all();
        foreach ($userNameInfo as $key => $value) {
            $userNameInfo[$key] = $value->pluck('tenant')->all();
        }


        $groupUser->update(
            [
                'number_users'        => $groupUser->tenants()->count(),
                'number_active_users' => $groupUser->tenants()->where('status', true)->count(),
                'data->names'         => $userNameInfo
            ]
        );
    }

    public function getJobUniqueId(GroupUser $groupUser): string
    {
        return $groupUser->id;
    }
}
