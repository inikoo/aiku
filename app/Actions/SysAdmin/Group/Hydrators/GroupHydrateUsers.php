<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:14:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Enums\SysAdmin\User\UserTypeEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateUsers
{
    use AsAction;

    private Group $group;
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }


    public function handle(Group $group): void
    {



        $numberUsers       = $group->users()->count();
        $numberActiveUsers = $group->users()->where('status', true)->count();

        $stats = [
            'number_users'                 => $numberUsers,
            'number_users_status_active'   => $numberActiveUsers,
            'number_users_status_inactive' => $numberUsers - $numberActiveUsers

        ];

        $statusCounts = User::selectRaw('type, count(*) as total')->where('group_id', $group->id)
            ->groupBy('type')
            ->pluck('total', 'type')->all();
        foreach (UserTypeEnum::cases() as $userType) {
            $stats['number_users_type_'.$userType->snake()] = Arr::get($statusCounts, $userType->value, 0);
        }


        $group->sysadminStats()->update($stats);
    }
}
